<?php

use App\Models\Order;
use App\Models\User;

test('authenticated user can rate their order successfully', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'rating' => null,
        'feedback' => null,
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('orders.rate', $order->id), [
            'rating' => 5,
            'feedback' => 'Delicious food and fast delivery!',
        ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Thank you for your feedback!',
            'rating' => 5,
        ]);

    $order->refresh();
    expect($order->rating)->toBe(5);
    expect($order->feedback)->toBe('Delicious food and fast delivery!');
});

test('rating validation rules are enforced', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
    ]);

    // Invalid rating (0 stars)
    $this->actingAs($user)
        ->post(route('orders.rate', $order->id), [
            'rating' => 0,
        ])
        ->assertSessionHasErrors(['rating']);

    // Invalid rating (6 stars)
    $this->actingAs($user)
        ->post(route('orders.rate', $order->id), [
            'rating' => 6,
        ])
        ->assertSessionHasErrors(['rating']);
});

test('user cannot rate an order belonging to another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $this->actingAs($user)
        ->postJson(route('orders.rate', $order->id), [
            'rating' => 4,
        ])
        ->assertStatus(403);

    $order->refresh();
    expect($order->rating)->toBeNull();
});
