<?php

namespace App\Support;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class CartState
{
    private const SESSION_KEY = 'cart.items';

    /**
     * @return Collection<int, array{product: Product, quantity: int, unit_price: float, line_total: float, unit: string, selected_options: array<string, mixed>|null}>
     */
    public function items(): Collection
    {
        if (auth()->check()) {
            return $this->authenticatedItems();
        }

        return $this->guestItems();
    }

    public function count(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    public function subtotal(): float
    {
        return (float) $this->items()->sum(
            fn (array $item): float => (float) ($item['line_total'] ?? ($item['unit_price'] * $item['quantity'])),
        );
    }

    public function add(Product $product, int $quantity = 1, ?array $selectedOptions = null): void
    {
        $weight = $selectedOptions['weight'] ?? null;
        $unitPrice = $product->priceForWeight($weight);
        $unitLabel = $weight ?: $product->unit;

        if (auth()->check()) {
            $item = CartItem::query()->firstOrNew([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ]);
            $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
            $item->product_name = $product->name;
            $item->product_slug = $product->slug;
            $item->sku = $product->sku;
            $item->category = $product->categoryName();
            $item->unit = $unitLabel;
            $item->unit_price = $unitPrice;
            $item->line_total = (float) $unitPrice * $item->quantity;
            $item->image_path = $product->image_path;

            if ($selectedOptions !== null) {
                $item->selected_options = $selectedOptions;
            }

            $item->save();

            return;
        }

        $items = $this->guestCartItems();
        $key = (string) $product->id;
        $currentQuantity = (int) ($items[$key]['quantity'] ?? 0);
        $items[$key] = [
            'quantity' => $currentQuantity + $quantity,
            'selected_options' => $selectedOptions ?? ($items[$key]['selected_options'] ?? null),
            'added_at' => $items[$key]['added_at'] ?? now()->timestamp,
        ];
        $this->storeGuestCartItems($items);
    }

    public function update(Product $product, int $quantity): bool
    {
        if (auth()->check()) {
            $item = CartItem::query()
                ->where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->first();

            if (! $item) {
                return false;
            }

            $weight = $item->selected_options['weight'] ?? null;
            $unitPrice = $product->priceForWeight($weight);

            $item->update([
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => (float) $unitPrice * $quantity,
            ]);

            return true;
        }

        $items = $this->guestCartItems();
        $key = (string) $product->id;

        if (! isset($items[$key])) {
            return false;
        }

        $items[$key]['quantity'] = $quantity;
        $this->storeGuestCartItems($items);

        return true;
    }

    public function remove(Product $product): void
    {
        if (auth()->check()) {
            CartItem::query()
                ->where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->delete();

            return;
        }

        $items = $this->guestCartItems();
        unset($items[(string) $product->id]);
        $this->storeGuestCartItems($items);
    }

    public function clear(): void
    {
        if (auth()->check()) {
            CartItem::query()
                ->where('user_id', auth()->id())
                ->delete();
        } else {
            session()->forget(self::SESSION_KEY);
        }
    }

    public function migrateGuestCartToUser(User $user): void
    {
        $guestItems = $this->guestCartItems();

        if ($guestItems === []) {
            return;
        }

        foreach ($guestItems as $productId => $itemData) {
            $product = Product::query()->find((int) $productId);

            if (! $product) {
                continue;
            }

            $selectedOptions = $itemData['selected_options'] ?? null;
            $weight = $selectedOptions['weight'] ?? null;
            $unitPrice = $product->priceForWeight($weight);
            $qty = (int) ($itemData['quantity'] ?? 1);

            $item = CartItem::query()->firstOrNew([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);

            $item->quantity = ($item->exists ? $item->quantity : 0) + $qty;
            $item->product_name = $product->name;
            $item->product_slug = $product->slug;
            $item->sku = $product->sku;
            $item->category = $product->categoryName();
            $item->unit = $weight ?: $product->unit;
            $item->unit_price = $unitPrice;
            $item->line_total = (float) $unitPrice * $item->quantity;
            $item->image_path = $product->image_path;
            $item->selected_options = $selectedOptions;
            $item->save();
        }

        session()->forget(self::SESSION_KEY);
    }

    /**
     * @return Collection<int, array{product: Product, quantity: int, unit_price: float, line_total: float, unit: string, selected_options: array<string, mixed>|null}>
     */
    private function authenticatedItems(): Collection
    {
        return auth()
            ->user()
            ->cartItems()
            ->with(['product' => fn ($query) => $query->active()->inStock()])
            ->latest('cart_items.created_at')
            ->get()
            ->map(function (CartItem $item): ?array {
                if (! $item->product) {
                    return null;
                }

                $weight = $item->selected_options['weight'] ?? null;
                $unitPrice = (float) ($item->unit_price > 0 ? $item->unit_price : $item->product->priceForWeight($weight));
                $lineTotal = (float) ($item->line_total > 0 ? $item->line_total : ($unitPrice * $item->quantity));
                $unitLabel = $item->unit ?: ($weight ?: $item->product->unit);

                return [
                    'product' => $item->product,
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'unit' => $unitLabel,
                    'selected_options' => $item->selected_options,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, array{product: Product, quantity: int, unit_price: float, line_total: float, unit: string, selected_options: array<string, mixed>|null}>
     */
    private function guestItems(): Collection
    {
        $guestItems = $this->guestCartItems();

        if ($guestItems === []) {
            return collect();
        }

        $orderedProductIds = array_map('intval', array_keys($guestItems));
        $products = Product::query()
            ->active()
            ->inStock()
            ->whereKey($orderedProductIds)
            ->get()
            ->keyBy('id');

        $validItems = [];

        foreach ($orderedProductIds as $productId) {
            $product = $products->get($productId);

            if (! $product) {
                continue;
            }

            $key = (string) $productId;
            $validItems[$key] = $guestItems[$key];
        }

        if ($validItems !== $guestItems) {
            $this->storeGuestCartItems($validItems);
        }

        return collect($orderedProductIds)
            ->map(function (int $productId) use ($products, $validItems): ?array {
                $product = $products->get($productId);

                if (! $product) {
                    return null;
                }

                $item = $validItems[(string) $productId];
                $weight = $item['selected_options']['weight'] ?? null;
                $unitPrice = $product->priceForWeight($weight);
                $quantity = (int) $item['quantity'];
                $unitLabel = $weight ?: $product->unit;

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $unitPrice * $quantity,
                    'unit' => $unitLabel,
                    'selected_options' => $item['selected_options'],
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @return array<string, array{quantity: int, selected_options: array<string, mixed>|null, added_at: int}>
     */
    private function guestCartItems(): array
    {
        $items = session()->get(self::SESSION_KEY, []);

        return is_array($items) ? $items : [];
    }

    /**
     * @param  array<string, array{quantity: int, selected_options: array<string, mixed>|null, added_at: int}>  $items
     */
    private function storeGuestCartItems(array $items): void
    {
        session()->put(self::SESSION_KEY, $items);
    }
}
