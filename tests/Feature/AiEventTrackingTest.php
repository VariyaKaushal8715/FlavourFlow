<?php

use App\AI\Contracts\AiAdapterInterface;
use App\AI\Contracts\AiEngineInterface;
use App\AI\Contracts\AiEventTrackerInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ai engine contracts and services resolve from container', function () {
    expect(app()->bound(AiEngineInterface::class))->toBeTrue();
    expect(app()->bound(AiAdapterInterface::class))->toBeTrue();
    expect(app()->bound(AiEventTrackerInterface::class))->toBeTrue();

    $engine = app(AiEngineInterface::class);
    expect($engine->isReady())->toBeTrue();
});

test('event tracker records user actions into ai_events database table', function () {
    $tracker = app(AiEventTrackerInterface::class);

    $event = $tracker->track('product_viewed', 'product', 42, [
        'name' => 'Kashmiri Saffron',
        'price' => 499.00,
    ]);

    expect($event)->not->toBeNull();
    expect($event->exists)->toBeTrue();
    expect($event->event_type)->toBe('product_viewed');
    expect($event->entity_type)->toBe('product');
    expect($event->entity_id)->toBe('42');
    expect($event->metadata['name'])->toBe('Kashmiri Saffron');

    $this->assertDatabaseHas('ai_events', [
        'event_type' => 'product_viewed',
        'entity_type' => 'product',
        'entity_id' => '42',
    ]);
});

test('admin ai status health page verifies all step 1 and step 2 checks successfully', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get(route('admin.ai.index'));

    $response->assertOk();
    $response->assertViewHas('isReady', true);
    $response->assertSee('Ready for Operation');
    $response->assertSee('Major User Event Types');
    $response->assertSee('✓ Verified');
});
