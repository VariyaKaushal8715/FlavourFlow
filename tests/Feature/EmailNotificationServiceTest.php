<?php

use App\Mail\AdminOrderMail;
use App\Mail\OrderCustomerMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\EmailNotificationService;
use Illuminate\Support\Facades\Mail;

test('payment success queues customer and admin emails', function (): void {
    Mail::fake();

    $user = User::factory()->create();
    $product = Product::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'Confirmed',
        'name' => 'Asha Patel',
        'email' => 'asha@example.com',
        'address' => '12 Market Road',
        'city' => 'Ahmedabad',
        'state' => 'Gujarat',
        'pincode' => '380009',
        'country' => 'India',
        'payment_method' => 'online',
        'subtotal' => 250,
        'delivery_charge' => 50,
        'discount_amount' => 25,
        'total_amount' => 275,
    ]);

    OrderItem::query()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'unit' => $product->unit,
        'quantity' => 2,
        'unit_price' => 125,
        'total_price' => 250,
    ]);

    $payment = Payment::query()->create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'razorpay_order_id' => 'order_test',
        'razorpay_payment_id' => 'pay_test',
        'amount' => 275,
        'currency' => 'INR',
        'status' => 'captured',
        'payment_method' => 'online',
        'paid_at' => now(),
    ]);

    app(EmailNotificationService::class)->sendPaymentSuccessful($payment);

    Mail::assertQueued(OrderCustomerMail::class, fn (OrderCustomerMail $mail): bool => $mail->event === 'payment_successful' && $mail->order->is($order));
    Mail::assertQueued(AdminOrderMail::class, fn (AdminOrderMail $mail): bool => $mail->event === 'payment_received' && $mail->payment?->is($payment));
});
