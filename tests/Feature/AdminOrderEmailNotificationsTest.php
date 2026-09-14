<?php

use App\Mail\AdminOrderMail;
use App\Mail\OrderCustomerMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('admin status update queues shipped order emails', function (): void {
    Mail::fake();

    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::factory()->create([
        'status' => 'Confirmed',
        'email' => 'customer@example.com',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'Shipped',
        ])
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('Shipped');
    expect($order->shipped_at)->not->toBeNull();

    Mail::assertQueued(OrderCustomerMail::class, fn (OrderCustomerMail $mail): bool => $mail->event === 'order_status_shipped' && $mail->order->is($order));
    Mail::assertQueued(AdminOrderMail::class, fn (AdminOrderMail $mail): bool => $mail->event === 'admin_order_status_shipped' && $mail->order?->is($order));
});

test('admin cancellation queues cancelled order emails', function (): void {
    Mail::fake();

    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::factory()->create([
        'status' => 'Pending',
        'email' => 'customer@example.com',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'Cancelled',
            'cancellation_reason' => 'Out of stock items',
        ])
        ->assertRedirect();

    $order->refresh();
    expect($order->status)->toBe('Cancelled');
    expect($order->cancellation_reason)->toBe('Out of stock items');
    expect($order->cancelled_at)->not->toBeNull();

    Mail::assertQueued(OrderCustomerMail::class, fn (OrderCustomerMail $mail): bool => $mail->event === 'order_status_cancelled' && $mail->order->is($order));
    Mail::assertQueued(AdminOrderMail::class, fn (AdminOrderMail $mail): bool => $mail->event === 'admin_order_status_cancelled' && $mail->order?->is($order));
});
