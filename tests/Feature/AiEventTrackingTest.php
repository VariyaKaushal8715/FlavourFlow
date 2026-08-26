<?php

use App\AI\Contracts\AiAdapterInterface;
use App\AI\Contracts\AiAnalyzerInterface;
use App\AI\Contracts\AiBrainInterface;
use App\AI\Contracts\AiContextBuilderInterface;
use App\AI\Contracts\AiEngineInterface;
use App\AI\Contracts\AiEventTrackerInterface;
use App\AI\Contracts\AiProviderInterface;
use App\AI\Contracts\AiRecommendationEngineInterface;
use App\AI\Core\AiDecisionResult;
use App\AI\Core\AiReasoningResponse;
use App\AI\Core\AiRecommendationResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ai engine contracts and services resolve from container (steps 1-5)', function () {
    expect(app()->bound(AiEngineInterface::class))->toBeTrue();
    expect(app()->bound(AiAdapterInterface::class))->toBeTrue();
    expect(app()->bound(AiEventTrackerInterface::class))->toBeTrue();
    expect(app()->bound(AiContextBuilderInterface::class))->toBeTrue();
    expect(app()->bound(AiAnalyzerInterface::class))->toBeTrue();
    expect(app()->bound(AiProviderInterface::class))->toBeTrue();
    expect(app()->bound(AiBrainInterface::class))->toBeTrue();
    expect(app()->bound(AiRecommendationEngineInterface::class))->toBeTrue();

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

test('ai brain service reasons over structured context for customer and admin', function () {
    $brain = app(AiBrainInterface::class);

    $context = [
        'total_events' => 5,
        'recently_viewed_products' => [
            ['product_id' => '1', 'name' => 'Kashmiri Red Chilli', 'viewed_at' => now()->toIso8601String()],
        ],
        'orders' => ['total_orders' => 1, 'total_spent' => 299.00],
    ];

    $customerResponse = $brain->reasonForCustomer($context, 'recommend something', 'en');
    expect($customerResponse)->toBeInstanceOf(AiReasoningResponse::class);
    expect($customerResponse->intent)->toBe('product_recommendation');
    expect($customerResponse->reasoning)->not->toBeEmpty();

    $adminResponse = $brain->reasonForAdmin($context, 'sales overview');
    expect($adminResponse)->toBeInstanceOf(AiReasoningResponse::class);
    expect($adminResponse->intent)->toBe('admin_sales_insight');
});

test('ai recommendation engine generates customer recommendations and admin decision signals', function () {
    $recEngine = app(AiRecommendationEngineInterface::class);

    $customerRecs = $recEngine->getCustomerRecommendations(type: 'personalized', limit: 4);
    expect($customerRecs)->toBeInstanceOf(AiRecommendationResult::class);
    expect($customerRecs->recommendationType)->toBe('personalized');
    expect($customerRecs->products)->toBeArray();

    $adminDecisions = $recEngine->getAdminDecisionSignals(days: 30);
    expect($adminDecisions)->toBeInstanceOf(AiDecisionResult::class);
    expect($adminDecisions->decisionType)->toBe('admin_business_decisions');
    expect($adminDecisions->winningProducts)->toBeArray();
    expect($adminDecisions->promotionNeeds)->toBeArray();
});

test('admin ai status health page verifies all step 1-5 checks successfully', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get(route('admin.ai.index'));

    $response->assertOk();
    $response->assertViewHas('isReady', true);
    $response->assertSee('Ready for Operation');
    $response->assertSee('AI Brain Service');
    $response->assertSee('AI Recommendation Engine Service');
    $response->assertSee('✓ Verified');
});
