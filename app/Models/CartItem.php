<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'product_id', 'quantity', 'selected_options'])]
class CartItem extends Model
{
    /** @var array<int, string> */
    protected $fillable = ['user_id', 'product_id', 'quantity', 'selected_options'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'selected_options' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
