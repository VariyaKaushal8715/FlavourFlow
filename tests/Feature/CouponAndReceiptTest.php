<?php

use App\Models\Offer;
use App\Models\Order;
use App\Models\User;

test('guest cannot apply coupon', function () {
    $this->postJson(route('checkout.coupon.apply'), [
        'coupon_code' => 'SPICE10',
    ])->assertRedirect(route('login'));
});

test('authenticated user can apply active valid coupon', function () {
    $user = User::factory()->create();
    $offer = Offer::create([
        'eyebrow' => 'Discount',
        'title' => 'Test Offer Title',
        'description' => 'Test Offer Description',
        'discount_label' => 'Save Rs. 50',
        'coupon_code' => 'TEST50',
        'is_active' => true,
        'terms' => 'Valid on orders above Rs. 0',
    ]);

    $this->actingAs($user)
        ->postJson(route('checkout.coupon.apply'), [
            'coupon_code' => '  test 50  ', // test spacing and capitalization ignoring
        ])
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'coupon' => [
                'code' => 'TEST50',
            ],
            'discount' => 0.00, // 0.00 because subtotal is 0 and discount cannot exceed subtotal
        ]);
});

test('cannot apply inactive or expired coupon', function () {
    $user = User::factory()->create();
    Offer::create([
        'eyebrow' => 'Discount',
        'title' => 'Inactive Offer Title',
        'description' => 'Inactive Offer Description',
        'discount_label' => 'Save 10%',
        'coupon_code' => 'INACTIVE',
        'is_active' => false,
    ]);

    $this->actingAs($user)
        ->postJson(route('checkout.coupon.apply'), [
            'coupon_code' => 'INACTIVE',
        ])
        ->assertStatus(422);
});

test('user can download their own receipt', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->get(route('account.orders.receipt', $order->order_number))
        ->assertSuccessful();

    // Verify it returns a PDF
    $response->assertHeader('Content-Type', 'application/pdf');
    expect($response->content())->toStartWith('%PDF-');
});

test('user cannot download others receipt', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user1->id]);

    $this->actingAs($user2)
        ->get(route('account.orders.receipt', $order->order_number))
        ->assertStatus(403);
});
