<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'sku',
    'category',
    'unit',
    'description',
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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
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

    /**
     * @return array<string, bool|float|int|string>
     */
    public function toHighlightData(): array
    {
        return [
            'name' => $this->name,
            'category' => $this->category,
            'description' => $this->description,
            'badge' => $this->badge,
            'price' => 'Rs. '.number_format((float) $this->price, 2),
            'compare_at_price' => $this->compare_at_price
                ? 'Rs. '.number_format((float) $this->compare_at_price, 2)
                : '',
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
        ];
    }
}
