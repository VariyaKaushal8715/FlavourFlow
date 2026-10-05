<?php

namespace App\Models;

use Database\Factories\ProductSortOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key',
    'label',
    'sort_field',
    'sort_direction',
    'display_order',
    'is_active',
    'is_default',
    'is_system',
])]
class ProductSortOption extends Model
{
    /** @use HasFactory<ProductSortOptionFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'sort_field',
        'sort_direction',
        'display_order',
        'is_active',
        'is_default',
        'is_system',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order', 'asc')->orderBy('id', 'asc');
    }

    public static function defaultOption(): ?self
    {
        return static::query()
            ->active()
            ->where('is_default', true)
            ->ordered()
            ->first()
            ?? static::query()->active()->ordered()->first();
    }

    /**
     * Apply this sort option's ordering rules to a Product query builder.
     */
    public function applyToQuery(Builder $query): Builder
    {
        $direction = strtolower($this->sort_direction ?: 'asc') === 'desc' ? 'desc' : 'asc';
        $field = strtolower(trim($this->sort_field ?: ''));
        $key = strtolower(trim($this->key));

        if ($key === 'featured' || $field === 'is_featured') {
            return $query->orderByDesc('is_featured')->orderByDesc('priority')->latest();
        }

        if ($key === 'rating' || $field === 'rating') {
            return $query->orderBy('rating', $direction)->orderByDesc('priority');
        }

        if ($key === 'price_asc' || ($field === 'price' && $direction === 'asc') || $key === 'price_range') {
            return $query->orderBy('price', 'asc')->orderByDesc('priority');
        }

        if ($key === 'price_desc' || ($field === 'price' && $direction === 'desc')) {
            return $query->orderBy('price', 'desc')->orderByDesc('priority');
        }

        if ($key === 'newest' || ($field === 'created_at' && $direction === 'desc')) {
            return $query->latest('created_at')->orderByDesc('priority');
        }

        if ($key === 'oldest' || ($field === 'created_at' && $direction === 'asc')) {
            return $query->oldest('created_at')->orderByDesc('priority');
        }

        if ($key === 'best_selling' || $field === 'best_selling') {
            return $query->withSum('orderItems', 'quantity')
                ->orderByDesc('order_items_sum_quantity')
                ->orderByDesc('priority');
        }

        if ($key === 'discount' || $field === 'discount') {
            $sqlDirection = strtoupper($direction);

            return $query->orderByRaw("(CASE WHEN compare_at_price > price THEN (compare_at_price - price) ELSE 0 END) {$sqlDirection}")
                ->orderByDesc('priority');
        }

        if ($key === 'name' || $field === 'name') {
            return $query->orderBy('name', $direction);
        }

        if (in_array($field, ['price', 'rating', 'name', 'priority', 'quantity', 'created_at', 'updated_at'], true)) {
            return $query->orderBy($field, $direction)->orderByDesc('priority');
        }

        return $query->orderByDesc('is_featured')->orderByDesc('priority')->latest();
    }

    /**
     * Restore default sort options list.
     */
    public static function seedDefaults(): void
    {
        $defaults = [
            [
                'key' => 'featured',
                'label' => 'Featured',
                'sort_field' => 'is_featured',
                'sort_direction' => 'desc',
                'display_order' => 1,
                'is_active' => true,
                'is_default' => true,
                'is_system' => true,
            ],
            [
                'key' => 'rating',
                'label' => 'Top Rated',
                'sort_field' => 'rating',
                'sort_direction' => 'desc',
                'display_order' => 2,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
            ],
            [
                'key' => 'price_asc',
                'label' => 'Price: Low to High',
                'sort_field' => 'price',
                'sort_direction' => 'asc',
                'display_order' => 3,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
            ],
            [
                'key' => 'price_desc',
                'label' => 'Price: High to Low',
                'sort_field' => 'price',
                'sort_direction' => 'desc',
                'display_order' => 4,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
            ],
            [
                'key' => 'newest',
                'label' => 'Newest Arrivals',
                'sort_field' => 'created_at',
                'sort_direction' => 'desc',
                'display_order' => 5,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
            ],
            [
                'key' => 'best_selling',
                'label' => 'Best Selling',
                'sort_field' => 'best_selling',
                'sort_direction' => 'desc',
                'display_order' => 6,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
            ],
            [
                'key' => 'name',
                'label' => 'Name: A to Z',
                'sort_field' => 'name',
                'sort_direction' => 'asc',
                'display_order' => 7,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
            ],
            [
                'key' => 'discount',
                'label' => 'Biggest Discounts',
                'sort_field' => 'discount',
                'sort_direction' => 'desc',
                'display_order' => 8,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
            ],
            [
                'key' => 'price_range',
                'label' => 'Price Range Filter',
                'sort_field' => 'price',
                'sort_direction' => 'asc',
                'display_order' => 9,
                'is_active' => true,
                'is_default' => false,
                'is_system' => true,
            ],
        ];

        foreach ($defaults as $data) {
            static::query()->updateOrCreate(
                ['key' => $data['key']],
                $data
            );
        }
    }
}
