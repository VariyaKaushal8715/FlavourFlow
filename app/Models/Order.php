<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            if (empty($order->order_number) && ! empty($order->order_id)) {
                $order->order_number = $order->order_id;
            }
            if (empty($order->order_id) && ! empty($order->order_number)) {
                $order->order_id = $order->order_number;
            }
            if (($order->total_amount === null || (float) $order->total_amount == 0.0) && $order->total) {
                $order->total_amount = $order->total;
            }
            if (($order->total === null || (float) $order->total == 0.0) && $order->total_amount) {
                $order->total = $order->total_amount;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
