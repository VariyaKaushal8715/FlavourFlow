<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'product_id',
    'product_name',
    'product_slug',
    'sku',
    'category',
    'unit',
    'quantity',
    'unit_price',
    'line_total',
    'image_path',
    'selected_options',
])]
class CartItem extends Model
{
    use HasFactory;

    protected $attributes = [
        'quantity' => 1,
        'unit_price' => 0,
        'line_total' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'selected_options' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function recalculateTotal(): void
    {
        $this->line_total = (float) $this->unit_price * $this->quantity;
    }
}
