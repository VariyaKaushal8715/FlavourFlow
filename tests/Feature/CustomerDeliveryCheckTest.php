<?php

namespace Tests\Feature;

use App\Models\DeliverySetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerDeliveryCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_check_location_when_mode_is_deliver_everywhere(): void
    {
        DeliverySetting::create([
            'mode' => 'all',
            'custom_locations' => [],
            'is_active' => true,
        ]);

        $response = $this->postJson(route('delivery.check'), [
            'country' => 'India',
            'state' => 'Gujarat',
            'city' => 'Surat',
        ]);

        $response->assertOk();
        $response->assertJson([
            'deliverable' => true,
            'mode' => 'all',
        ]);

        $intlResponse = $this->postJson(route('delivery.check'), [
            'country' => 'United States',
        ]);

        $intlResponse->assertOk();
        $intlResponse->assertJson([
            'deliverable' => true,
            'mode' => 'all',
        ]);
    }

    public function test_customer_location_check_with_custom_locations(): void
    {
        DeliverySetting::create([
            'mode' => 'custom',
            'custom_locations' => [
                ['country' => 'India', 'state' => 'Gujarat', 'city' => 'Surat'],
                ['country' => 'United States', 'state' => null, 'city' => null],
                ['country' => 'United Kingdom', 'state' => null, 'city' => null],
            ],
            'is_active' => true,
        ]);

        // Deliverable India city
        $res1 = $this->postJson(route('delivery.check'), [
            'country' => 'India',
            'state' => 'Gujarat',
            'city' => 'Surat',
        ]);
        $res1->assertOk();
        $res1->assertJson([
            'deliverable' => true,
            'mode' => 'custom',
        ]);

        // Undeliverable India city
        $res2 = $this->postJson(route('delivery.check'), [
            'country' => 'India',
            'state' => 'Gujarat',
            'city' => 'Ahmedabad',
        ]);
        $res2->assertOk();
        $res2->assertJson([
            'deliverable' => false,
            'message' => 'Sorry, we don’t deliver to this location.',
        ]);

        // Deliverable International country
        $res3 = $this->postJson(route('delivery.check'), [
            'country' => 'USA',
        ]);
        $res3->assertOk();
        $res3->assertJson([
            'deliverable' => true,
            'mode' => 'custom',
        ]);

        // Undeliverable International country
        $res4 = $this->postJson(route('delivery.check'), [
            'country' => 'Australia',
        ]);
        $res4->assertOk();
        $res4->assertJson([
            'deliverable' => false,
            'message' => 'Sorry, we don’t deliver to this location.',
        ]);
    }

    public function test_checkout_allowed_for_deliverable_location(): void
    {
        DeliverySetting::create([
            'mode' => 'custom',
            'custom_locations' => [
                ['country' => 'India', 'state' => 'Gujarat', 'city' => 'Surat'],
            ],
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 10, 'price' => 500.00]);

        // Add to cart
        $this->actingAs($user)->post(route('cart.store', $product->slug), [
            'quantity' => 1,
            'unit' => '100g',
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'name' => 'Kaushal Variya',
            'mobile' => '9876543210',
            'email' => 'kaushal@example.com',
            'address' => '123 Spices Street',
            'city' => 'Surat',
            'state' => 'Gujarat',
            'pincode' => '395001',
            'country' => 'India',
            'payment_method' => 'cod',
            'delivery_option' => 'standard',
        ]);

        $response->assertRedirect(route('checkout.success'));
        $this->assertDatabaseHas('orders', [
            'name' => 'Kaushal Variya',
            'city' => 'Surat',
            'state' => 'Gujarat',
            'country' => 'India',
        ]);
    }

    public function test_checkout_blocked_for_undeliverable_location(): void
    {
        DeliverySetting::create([
            'mode' => 'custom',
            'custom_locations' => [
                ['country' => 'India', 'state' => 'Gujarat', 'city' => 'Surat'],
            ],
            'is_active' => true,
        ]);

        $user = User::factory()->create();
        $product = Product::factory()->create(['quantity' => 10, 'price' => 500.00]);

        // Add to cart
        $this->actingAs($user)->post(route('cart.store', $product->slug), [
            'quantity' => 1,
            'unit' => '100g',
        ]);

        // Attempt checkout to Mumbai (not in allowed list)
        $response = $this->actingAs($user)->from(route('checkout.index'))->post(route('checkout.store'), [
            'name' => 'Kaushal Variya',
            'mobile' => '9876543210',
            'email' => 'kaushal@example.com',
            'address' => '456 Marine Drive',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'country' => 'India',
            'payment_method' => 'cod',
            'delivery_option' => 'standard',
        ]);

        $response->assertSessionHasErrors([
            'country' => 'Sorry, we don’t deliver to this location.',
        ]);

        $this->assertDatabaseMissing('orders', [
            'name' => 'Kaushal Variya',
            'city' => 'Mumbai',
        ]);
    }
}
