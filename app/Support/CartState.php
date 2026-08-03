<?php

namespace App\Support;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Collection;

class CartState
{
    private const SESSION_KEY = 'cart.items';

    /**
     * @return Collection<int, array{product: Product, quantity: int, selected_options: array<string, mixed>|null}>
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
        return $this->items()->sum(
            fn (array $item): float => (float) $item['product']->price * $item['quantity'],
        );
    }

    public function add(Product $product, int $quantity = 1, ?array $selectedOptions = null): void
    {
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
            $item->unit = $product->unit;
            $item->unit_price = $product->price;
            $item->line_total = (float) $product->price * $item->quantity;
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
            return CartItem::query()
                ->where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->update([
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'line_total' => (float) $product->price * $quantity,
                ]) > 0;
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

    /**
     * @return Collection<int, array{product: Product, quantity: int, selected_options: array<string, mixed>|null}>
     */
    private function authenticatedItems(): Collection
    {
        return auth()
            ->user()
            ->cartItems()
            ->with(['product' => fn ($query) => $query->active()->inStock()])
            ->latest('cart_items.created_at')
            ->get()
            ->map(fn (CartItem $item): ?array => $item->product ? [
                'product' => $item->product,
                'quantity' => $item->quantity,
                'selected_options' => $item->selected_options,
            ] : null)
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, array{product: Product, quantity: int, selected_options: array<string, mixed>|null}>
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

                return [
                    'product' => $product,
                    'quantity' => (int) $item['quantity'],
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
