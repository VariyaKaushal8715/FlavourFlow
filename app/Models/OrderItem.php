<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (OrderItem $item) {
            if (($item->total_price === null || (float) $item->total_price == 0.0) && $item->line_total) {
                $item->total_price = $item->line_total;
            }
            if (($item->line_total === null || (float) $item->line_total == 0.0) && $item->total_price) {
                $item->line_total = $item->total_price;
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
