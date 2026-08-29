<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'sku',
    'category',
    'unit',
    'description',
    'long_description',
    'highlights',
    'ingredients',
    'usage_instructions',
    'origin',
    'badge',
    'price',
    'compare_at_price',
    'quantity',
    'low_stock_threshold',
    'rating',
    'priority',
    'image_path',
    'is_featured',
    'is_active',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $attributes = [
        'badge' => 'New',
        'unit' => '100 g',
        'quantity' => 0,
        'low_stock_threshold' => 5,
        'rating' => 4.5,
        'priority' => 50,
        'is_featured' => false,
        'is_active' => true,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'highlights' => 'array',
            'quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'rating' => 'decimal:1',
            'priority' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'low_stock_threshold');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $query, string $search): void {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        });
    }

    public function isLowStock(): bool
    {
        return $this->quantity > 0 && $this->quantity <= $this->low_stock_threshold;
    }

    public function stockLabel(): string
    {
        if ($this->quantity === 0) {
            return 'Out of stock';
        }

        if ($this->isLowStock()) {
            return "Only {$this->quantity} left";
        }

        return 'In stock';
    }

    public function uploadedImageStoragePath(): ?string
    {
        if (! $this->image_path || ! Str::startsWith($this->image_path, 'storage/products/')) {
            return null;
        }

        return Str::after($this->image_path, 'storage/');
    }

    public function formattedPrice(): string
    {
        return 'Rs. '.number_format((float) $this->price, 2);
    }

    public function formattedComparePrice(): ?string
    {
        return $this->compare_at_price
            ? 'Rs. '.number_format((float) $this->compare_at_price, 2)
            : null;
    }

    public function categoryName(): string
    {
        return $this->category ?: 'Uncategorized';
    }

    /**
     * @return array<string, array{key: string, label: string, weight: string, multiplier: float, price: float, formatted_price: string}>
     */
    public function availableVariants(): array
    {
        $basePrice = (float) $this->price;
        $options = [
            '100g' => ['label' => '100g', 'weight' => '100g', 'multiplier' => 1.0],
            '250g' => ['label' => '250g', 'weight' => '250g', 'multiplier' => 2.4],
            '500g' => ['label' => '500g', 'weight' => '500g', 'multiplier' => 4.5],
            '1kg' => ['label' => '1kg', 'weight' => '1kg', 'multiplier' => 8.5],
        ];

        $variants = [];
        foreach ($options as $key => $opt) {
            $variantPrice = round($basePrice * $opt['multiplier'], 2);
            $variants[$key] = [
                'key' => $key,
                'label' => $opt['label'],
                'weight' => $opt['weight'],
                'multiplier' => $opt['multiplier'],
                'price' => $variantPrice,
                'formatted_price' => 'Rs. '.number_format($variantPrice, 2),
            ];
        }

        return $variants;
    }

    public function priceForWeight(?string $weight): float
    {
        $variants = $this->availableVariants();
        if ($weight && isset($variants[$weight])) {
            return $variants[$weight]['price'];
        }

        return (float) $this->price;
    }

    /**
     * @return array<string, bool|float|int|string>
     */
    public function toHighlightData(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'category' => $this->categoryName(),
            'description' => $this->description,
            'badge' => $this->badge,
            'price' => $this->formattedPrice(),
            'compare_at_price' => $this->formattedComparePrice() ?? '',
            'metric' => number_format((float) $this->rating, 1).' rating',
            'image' => $this->image_path ?: 'images/flavourflow-mark.png',
            'sku' => $this->sku ?? '',
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'stock_label' => $this->stockLabel(),
            'in_stock' => $this->quantity > 0,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'priority' => $this->priority,
            'rating' => (float) $this->rating,
            'url' => route('products.show', ['product' => $this->slug]),
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
