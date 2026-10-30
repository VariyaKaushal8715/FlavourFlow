<?php

use App\AI\Contracts\AiConsultantInterface;
use App\AI\Contracts\AiKnowledgeBaseInterface;
use App\AI\Contracts\AiKnowledgeIngestionInterface;
use App\AI\Knowledge\AiCurriculumRegistry;
use App\AI\Models\AiKnowledgeDomain;
use App\AI\Models\AiKnowledgeGap;
use App\AI\Models\AiKnowledgeItem;
use App\AI\Models\AiKnowledgeSource;
use App\AI\Models\AiKnowledgeTopic;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

beforeEach(function (): void {
    // Seed default 18-domain curriculum for tests
    app(AiKnowledgeIngestionInterface::class)->seedDefaultCurriculum();
});

test('curriculum registry defines all 18 e-commerce domains with multi-tier topics', function (): void {
    $domains = AiCurriculumRegistry::getCurriculum();

    expect($domains)->toHaveCount(18);

    $domainCodes = collect($domains)->pluck('code')->all();
    expect($domainCodes)->toContain(
        'ecommerce_fundamentals',
        'business_strategy',
        'storefront_ux',
        'product_catalog',
        'inventory_warehouse',
        'cart_checkout_pricing',
        'orders_fulfillment',
        'payments_finance',
        'crm_customer_management',
        'marketing_advertising',
        'seo_content',
        'analytics_bi',
        'security_fraud_privacy',
        'notifications_automation',
        'technical_architecture',
        'legal_tax_compliance',
        'ai_powered_commerce',
        'advanced_architecture'
    );

    // Verify beginner, intermediate, and advanced tiers exist in every domain
    foreach ($domains as $d) {
        $levels = collect($d['topics'])->pluck('level')->unique()->all();
        expect($levels)->toContain('beginner');
        expect($levels)->toContain('intermediate');
        expect($levels)->toContain('advanced');
    }
});

test('ingestion service populates all 18 domains and 54 topics with citations and versioning', function (): void {
    expect(AiKnowledgeDomain::count())->toBe(18);
    expect(AiKnowledgeTopic::count())->toBeGreaterThanOrEqual(54);
    expect(AiKnowledgeItem::count())->toBeGreaterThanOrEqual(54);
    expect(AiKnowledgeSource::count())->toBeGreaterThanOrEqual(54);

    // Verify idempotency (re-running ingestion does not duplicate)
    app(AiKnowledgeIngestionInterface::class)->seedDefaultCurriculum();

    expect(AiKnowledgeDomain::count())->toBe(18);
    expect(AiKnowledgeTopic::count())->toBeGreaterThanOrEqual(54);
});

test('knowledge base performs RAG lexical and keyword retrieval with source citations', function (): void {
    /** @var AiKnowledgeBaseInterface $kb */
    $kb = app(AiKnowledgeBaseInterface::class);

    // Query checkout concurrency
    $results = $kb->search('database transactions lockForUpdate inventory checkout');

    expect($results['items'])->not->toBeEmpty();
    $first = $results['items'][0];

    expect($first)->toHaveKeys(['title', 'concept', 'rules', 'workflows', 'confidence']);
    expect($first['confidence'])->toBeGreaterThanOrEqual(0.5);
    expect($first['source_citation'])->not->toBeEmpty();
});

test('memory layers are separated and queryable by memory type', function (): void {
    /** @var AiKnowledgeBaseInterface $kb */
    $kb = app(AiKnowledgeBaseInterface::class);

    $techResults = $kb->retrieveByMemoryType('technical_architecture');
    expect($techResults)->not->toBeEmpty();
    foreach ($techResults as $res) {
        expect($res['memory_type'])->toBe('technical_architecture');
    }

    $bizResults = $kb->retrieveByMemoryType('store_business');
    expect($bizResults)->not->toBeEmpty();
    foreach ($bizResults as $res) {
        expect($res['memory_type'])->toBe('store_business');
    }
});

test('knowledge base logs an identified knowledge gap when search returns low confidence', function (): void {
    /** @var AiKnowledgeBaseInterface $kb */
    $kb = app(AiKnowledgeBaseInterface::class);

    $unmappedQuery = 'quantum computing cryogenic semiconductor fusion';

    $results = $kb->search($unmappedQuery);
    expect($results['items'])->toBeEmpty();

    // Verify self-detected knowledge gap was recorded
    $gap = AiKnowledgeGap::where('query_or_topic', $unmappedQuery)->first();
    expect($gap)->not->toBeNull();
    expect($gap->status)->toBe('identified');
    expect($gap->description)->toContain('Zero matching verified knowledge items');
});

test('domain consultant produces structured recommendations in multiple languages', function (): void {
    /** @var AiConsultantInterface $consultant */
    $consultant = app(AiConsultantInterface::class);

    // English business query
    $enResponse = $consultant->consult('What is the gross margin target?', ['role' => 'business_intelligence'], ['language' => 'en']);
    expect($enResponse['answer'])->not->toBeEmpty();
    expect($enResponse['language'])->toBe('en');
    expect($enResponse['role'])->toBe('business_intelligence');
    expect($enResponse['recommendations'])->toBeArray();

    // Gujarati technical query
    $guResponse = $consultant->consult('ઓર્ડર કન્ફર્મ કરવા માટે UPI પેમેન્ટ કેવી રીતે કામ કરે છે?', ['role' => 'technical_consultant'], ['language' => 'gu']);
    expect($guResponse['answer'])->not->toBeEmpty();
    expect($guResponse['language'])->toBe('gu');

    // Hindi implementation query
    $hiResponse = $consultant->consult('कार्ट परित्याग (Cart Abandonment) कैसे कम करें?', ['role' => 'implementation_copilot'], ['language' => 'hi']);
    expect($hiResponse['answer'])->not->toBeEmpty();
    expect($hiResponse['language'])->toBe('hi');
});

test('store business intelligence diagnostic calculates real sales, inventory, and funnel metrics', function (): void {
    // Create test products and orders
    Product::factory()->create(['is_active' => true, 'quantity' => 100, 'price' => 250]);
    Product::factory()->create(['is_active' => true, 'quantity' => 2, 'price' => 150]); // Low stock
    Product::factory()->create(['is_active' => true, 'quantity' => 0, 'price' => 99]);  // Out of stock

    $user = User::factory()->create();
    Order::factory()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-BI-101',
        'status' => 'Delivered',
        'total_amount' => 500.00,
    ]);

    /** @var AiConsultantInterface $consultant */
    $consultant = app(AiConsultantInterface::class);
    $bi = $consultant->diagnoseStore();

    expect($bi)->toHaveKeys([
        'sales_analytics',
        'inventory_health',
        'cart_and_conversion',
        'unit_economics',
        'strengths',
        'risks_identified',
        'strategic_recommendations',
    ]);

    expect($bi['sales_analytics']['total_orders'])->toBeGreaterThanOrEqual(1);
    expect($bi['sales_analytics']['total_revenue'])->toBeGreaterThanOrEqual(500.00);
    expect($bi['inventory_health']['low_stock_count'])->toBeGreaterThanOrEqual(1);
    expect($bi['inventory_health']['out_of_stock_count'])->toBeGreaterThanOrEqual(1);
});

test('admin can access knowledge base dashboard and management endpoints', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    // Admin dashboard loads with 18 domains and checks passed
    $response = $this->actingAs($admin)->get(route('admin.ai.dashboard'));
    $response->assertOk()
        ->assertSee('E-Commerce Domain Intelligence')
        ->assertSee('18 Master Domains Loaded')
        ->assertSee('Curriculum Topics');

    // Admin seed curriculum endpoint
    $seedRes = $this->actingAs($admin)->postJson(route('admin.ai.knowledge.seed'));
    $seedRes->assertOk()
        ->assertJson(['success' => true]);

    // Admin consult endpoint
    $consultRes = $this->actingAs($admin)->postJson(route('admin.ai.knowledge.consult'), [
        'role' => 'technical_consultant',
        'query' => 'Explain database locking for inventory',
        'language' => 'en',
    ]);
    $consultRes->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['role', 'language', 'answer', 'confidence', 'sources', 'recommendations']]);

    // Admin search endpoint
    $searchRes = $this->actingAs($admin)->getJson(route('admin.ai.knowledge.search', ['q' => 'inventory']));
    $searchRes->assertOk()
        ->assertJsonPath('success', true);

    // Admin resolve gap endpoint
    $gap = AiKnowledgeGap::create([
        'query_or_topic' => 'Test unmapped query',
        'description' => 'Test description',
        'recommended_action' => 'Ingest official documentation',
        'status' => 'identified',
    ]);

    $resolveRes = $this->actingAs($admin)->postJson(route('admin.ai.knowledge.gap.resolve', $gap->id), [
        'notes' => 'Resolved in test',
    ]);
    $resolveRes->assertOk()
        ->assertJsonPath('success', true);

    expect($gap->fresh()->status)->toBe('resolved');
});

test('unauthenticated users cannot access admin knowledge routes', function (): void {
    $this->post(route('admin.ai.knowledge.seed'))->assertStatus(302);
    $this->post(route('admin.ai.knowledge.consult'), ['query' => 'test'])->assertStatus(302);
    $this->get(route('admin.ai.knowledge.search', ['q' => 'test']))->assertStatus(302);
});

test('customer chatbot uses e-commerce knowledge base for store policy questions', function (): void {
    // When customer asks return policy
    $res = $this->postJson(route('api.chatbot'), [
        'message' => 'What is your refund and return policy?',
    ]);

    $res->assertOk()
        ->assertJson(['success' => true]);

    // Should NOT say "What is your refund and return policy? is currently not available in our catalog"
    expect($res->json('reply'))->not->toContain('is currently not available in our catalog');
    expect($res->json('reply'))->toContain('return');
});
