<?php

use App\Models\Order;
use App\Models\OrderNotification;
use App\Models\User;

test('order status changes trigger notifications automatically', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'Pending',
    ]);

    // 1. Confirmed Status
    $order->update(['status' => 'Confirmed']);
    $notif1 = OrderNotification::where('order_id', $order->id)->where('status', 'Confirmed')->first();
    expect($notif1)->not->toBeNull();
    expect($notif1->message)->toBe("Order #{$order->order_number} has been confirmed.");

    // 2. Shipped Status
    $order->update(['status' => 'Shipped']);
    $notif2 = OrderNotification::where('order_id', $order->id)->where('status', 'Shipped')->first();
    expect($notif2)->not->toBeNull();
    expect($notif2->message)->toBe("Order #{$order->order_number} has been shipped.");

    // 3. Out for Delivery Status
    $order->update(['status' => 'Out for Delivery']);
    $notif3 = OrderNotification::where('order_id', $order->id)->where('status', 'Out for Delivery')->first();
    expect($notif3)->not->toBeNull();
    expect($notif3->message)->toBe("Order #{$order->order_number} is out for delivery.");

    // 4. Delivered Status
    $order->update(['status' => 'Delivered']);
    $notif4 = OrderNotification::where('order_id', $order->id)->where('status', 'Delivered')->first();
    expect($notif4)->not->toBeNull();
    expect($notif4->message)->toBe("Order #{$order->order_number} has been delivered.");

    // 5. Cancelled Status
    $order->update(['status' => 'Cancelled']);
    $notif5 = OrderNotification::where('order_id', $order->id)->where('status', 'Cancelled')->first();
    expect($notif5)->not->toBeNull();
    expect($notif5->message)->toBe("Order #{$order->order_number} has been cancelled.");
});

test('opening order details or track page marks notifications as read', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'Pending',
    ]);

    // Trigger notification
    $order->update(['status' => 'Confirmed']);
    $notif = OrderNotification::where('order_id', $order->id)->first();
    expect($notif->read_at)->toBeNull();

    // Visit show page
    $this->actingAs($user)
        ->get(route('account.orders.show', $order->order_number))
        ->assertSuccessful();

    $notif->refresh();
    expect($notif->read_at)->not->toBeNull();

    // Reset read_at to test track page
    $notif->update(['read_at' => null]);

    // Visit track page
    $this->actingAs($user)
        ->get(route('account.orders.track', $order->order_number))
        ->assertSuccessful();

    $notif->refresh();
    expect($notif->read_at)->not->toBeNull();
});

test('guest cannot fetch notifications', function () {
    $this->get(route('account.orders'))
        ->assertRedirect(route('login'));
});
