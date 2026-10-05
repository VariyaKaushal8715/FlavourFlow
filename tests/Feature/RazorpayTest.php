<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

test('online checkout creates order with awaiting_payment status and redirects to payment page', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 100, 'quantity' => 10]);

    CartItem::query()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'product_slug' => $product->slug,
        'sku' => $product->sku,
        'category' => $product->categoryName(),
        'unit' => $product->unit,
        'quantity' => 2,
        'unit_price' => $product->price,
        'line_total' => $product->price * 2,
        'image_path' => $product->image_path,
    ]);

    $response = $this->actingAs($user)
        ->post(route('checkout.store'), [
            'name' => 'John Doe',
            'mobile' => '9999999999',
            'email' => 'john@example.com',
            'address' => '123 Main St',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380009',
            'country' => 'India',
            'payment_method' => 'online',
        ]);

    $order = Order::query()->where('user_id', $user->id)->first();
    
    expect($order)->not->toBeNull();
    expect($order->status)->toBe('Confirmed');
    expect($order->payment_status)->toBe('awaiting_payment');
    
    $response->assertRedirect(route('checkout.payment', $order));
});

test('webhook without signature returns error', function () {
    $response = $this->postJson(route('razorpay.webhook'), [
        'event' => 'payment.captured',
    ]);
    
    // It should either return 500 if no secret configured or 403 if signature is invalid
    $response->assertStatus(500); 
});
