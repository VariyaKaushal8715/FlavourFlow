<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'name',
        'mobile',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'country',
        'payment_method',
        'delivery_option',
        'delivery_days',
        'subtotal',
        'delivery_charge',
        'total_amount',
        'confirmed_at',
        'shipped_at',
        'out_for_delivery_at',
        'delivered_at',
        'coupon_code',
        'coupon_id',
        'discount_amount',
        'earned_coupon_id',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::updated(function (Order $order) {
            if ($order->isDirty('status')) {
                $status = $order->status;
                $orderNumber = $order->order_number;

                $messages = [
                    'Confirmed' => "Order #{$orderNumber} has been confirmed.",
                    'Cancelled' => "Order #{$orderNumber} has been cancelled.",
                    'Shipped' => "Order #{$orderNumber} has been shipped.",
                    'Out for Delivery' => "Order #{$orderNumber} is out for delivery.",
                    'Delivered' => "Order #{$orderNumber} has been delivered.",
                ];

                if (isset($messages[$status])) {
                    OrderNotification::create([
                        'user_id' => $order->user_id,
                        'order_id' => $order->id,
                        'status' => $status,
                        'message' => $messages[$status],
                    ]);
                }
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function returnRequest(): HasOne
    {
        return $this->hasOne(ReturnRequest::class);
    }

    public function refundRequest(): HasOne
    {
        return $this->hasOne(RefundRequest::class);
    }

    public function deliveryNotifications(): HasMany
    {
        return $this->hasMany(OrderDeliveryNotification::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function earnedCoupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'earned_coupon_id');
    }

    public function generatedCoupons(): HasMany
    {
        return $this->hasMany(Coupon::class, 'source_order_id');
    }
}
