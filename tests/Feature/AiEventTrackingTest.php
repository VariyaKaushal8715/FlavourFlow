<?php

use App\AI\Contracts\AiAdapterInterface;
use App\AI\Contracts\AiAnalyzerInterface;
use App\AI\Contracts\AiContextBuilderInterface;
use App\AI\Contracts\AiEngineInterface;
use App\AI\Contracts\AiEventTrackerInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ai engine contracts and services resolve from container', function () {
    expect(app()->bound(AiEngineInterface::class))->toBeTrue();
    expect(app()->bound(AiAdapterInterface::class))->toBeTrue();
    expect(app()->bound(AiEventTrackerInterface::class))->toBeTrue();
    expect(app()->bound(AiContextBuilderInterface::class))->toBeTrue();
    expect(app()->bound(AiAnalyzerInterface::class))->toBeTrue();

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

test('ai context builder extracts structured user context from recorded events', function () {
    $tracker = app(AiEventTrackerInterface::class);
    $builder = app(AiContextBuilderInterface::class);

    $tracker->track('product_viewed', 'product', 10, ['name' => 'Kashmiri Chilli', 'category' => 'Spices']);
    $tracker->track('cart_added', 'product', 10, ['name' => 'Kashmiri Chilli', 'quantity' => 2]);
    $tracker->track('checkout_started', 'cart', null, ['total' => 598.00]);

    $context = $builder->buildContext();

    expect($context['total_events'])->toBeGreaterThanOrEqual(3);
    expect($context['recently_viewed_products'])->not->toBeEmpty();
    expect($context['cart_activity']['total_added'])->toBeGreaterThanOrEqual(1);
    expect($context['abandoned_cart_signals']['has_abandoned_cart'])->toBeTrue();
});

test('ai analyzer produces deterministic purchase intent and pattern insights', function () {
    $tracker = app(AiEventTrackerInterface::class);
    $builder = app(AiContextBuilderInterface::class);
    $analyzer = app(AiAnalyzerInterface::class);

    $tracker->track('product_viewed', 'product', 5, ['name' => 'Organic Turmeric', 'category' => 'Ground Spices']);
    $tracker->track('cart_added', 'product', 5, ['name' => 'Organic Turmeric']);

    $context = $builder->buildContext();
    $analysis = $analyzer->analyze($context);

    expect($analysis)->toHaveKeys(['purchase_intent', 'product_interest', 'category_preference', 'cart_abandonment', 'recommendation_signals']);
    expect($analysis['purchase_intent']['score'])->toBeGreaterThanOrEqual(70);
    expect($analysis['recommendation_signals']['trigger'])->not->toBeEmpty();
});

test('admin ai status health page verifies step 1, 2 and 3 checks successfully', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get(route('admin.ai.index'));

    $response->assertOk();
    $response->assertViewHas('isReady', true);
    $response->assertSee('Ready for Operation');
    $response->assertSee('AI Context Builder Service');
    $response->assertSee('AI Analyzer Service');
    $response->assertSee('✓ Verified');
});
