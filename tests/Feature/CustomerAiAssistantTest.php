<?php

use App\Models\Product;
use App\Models\User;

test('guest user can chat with the assistant and it returns success', function () {
    // Create some active products
    $product = Product::factory()->create([
        'name' => 'Kashmiri Red Chilli',
        'category' => 'Whole Spices',
        'price' => 299.00,
        'is_active' => true,
    ]);

    $response = $this->postJson(route('ai.chat'), [
        'message' => 'Recommend Kashmiri Red Chilli',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'response' => [
                'id',
                'sender',
                'message',
                'language',
                'intent',
                'products',
                'actions',
                'timestamp',
            ],
            'provider_available',
        ]);

    expect($response['success'])->toBeTrue();
    expect($response['response']['intent'])->not->toBeEmpty();
});

test('authenticated user can chat and history is stored and cleared', function () {
    $user = User::factory()->create();

    // 1. Initial Chat
    $response = $this->actingAs($user)->postJson(route('ai.chat'), [
        'message' => 'Recommend some whole spices',
    ]);

    $response->assertOk();
    expect($response['success'])->toBeTrue();

    // 2. Retrieve history
    $historyResponse = $this->actingAs($user)->getJson(route('ai.chat.history'));
    $historyResponse->assertOk()
        ->assertJsonStructure([
            'success',
            'history',
            'provider_available',
        ]);

    expect(count($historyResponse['history']))->toBeGreaterThan(0);

    // 3. Clear history
    $clearResponse = $this->actingAs($user)->deleteJson(route('ai.chat.clear'));
    $clearResponse->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'provider_available',
        ]);

    // 4. Verify history is empty now
    $emptyHistoryResponse = $this->actingAs($user)->getJson(route('ai.chat.history'));
    $emptyHistoryResponse->assertOk();
    expect($emptyHistoryResponse['history'])->toBeEmpty();
});
