<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
<<<<<<< HEAD
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
=======
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'product_id', 'quantity', 'selected_options'])]
class CartItem extends Model
{
    /** @var array<int, string> */
    protected $fillable = ['user_id', 'product_id', 'quantity', 'selected_options'];

>>>>>>> 6f8896fa25ab3b15892b2a80db332e79373b28ab
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
<<<<<<< HEAD
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

=======
            'selected_options' => 'array',
        ];
    }

>>>>>>> 6f8896fa25ab3b15892b2a80db332e79373b28ab
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

<<<<<<< HEAD
    public function recalculateTotal(): void
    {
        $this->line_total = (float) $this->unit_price * $this->quantity;
=======
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
>>>>>>> 6f8896fa25ab3b15892b2a80db332e79373b28ab
    }
}
