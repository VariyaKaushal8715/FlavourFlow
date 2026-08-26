<?php

use App\AI\Contracts\AiAdapterInterface;
use App\AI\Contracts\AiAnalyzerInterface;
use App\AI\Contracts\AiBrainInterface;
use App\AI\Contracts\AiContextBuilderInterface;
use App\AI\Contracts\AiEngineInterface;
use App\AI\Contracts\AiEventTrackerInterface;
use App\AI\Contracts\AiLanguageUnderstandingInterface;
use App\AI\Contracts\AiProviderInterface;
use App\AI\Contracts\AiRecommendationEngineInterface;
use App\AI\Core\AiParsedIntent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ai engine contracts and services resolve from container (steps 1-7)', function () {
    expect(app()->bound(AiEngineInterface::class))->toBeTrue();
    expect(app()->bound(AiAdapterInterface::class))->toBeTrue();
    expect(app()->bound(AiEventTrackerInterface::class))->toBeTrue();
    expect(app()->bound(AiContextBuilderInterface::class))->toBeTrue();
    expect(app()->bound(AiAnalyzerInterface::class))->toBeTrue();
    expect(app()->bound(AiProviderInterface::class))->toBeTrue();
    expect(app()->bound(AiBrainInterface::class))->toBeTrue();
    expect(app()->bound(AiRecommendationEngineInterface::class))->toBeTrue();
    expect(app()->bound(AiLanguageUnderstandingInterface::class))->toBeTrue();

    $engine = app(AiEngineInterface::class);
    expect($engine->isReady())->toBeTrue();
});

test('multilingual language understanding service detects languages and extracts entities', function () {
    $nlu = app(AiLanguageUnderstandingInterface::class);

    // 1. Gujarati Script
    expect($nlu->detectLanguage('મારે લાલ મરચું જોઈએ છે'))->toBe('gu');

    // 2. GujEnglish Transliteration
    expect($nlu->detectLanguage('kem cho, marcha aapo 300 rs nu'))->toBe('gujenglish');

    // 3. Devanagari Hindi Script
    expect($nlu->detectLanguage('मुझे गरम मसाला चाहिए'))->toBe('hi');

    // 4. Hinglish Transliteration
    expect($nlu->detectLanguage('bhai accha haldi powder milega kya'))->toBe('hinglish');

    // 5. English
    expect($nlu->detectLanguage('looking for organic Kashmiri chilli under 400'))->toBe('en');

    // Entity & Intent extraction test
    $parsed = $nlu->understand('bhai accha haldi powder milega kya 200 rupees tak');
    expect($parsed)->toBeInstanceOf(AiParsedIntent::class);
    expect($parsed->language)->toBe('hinglish');
    expect($parsed->entities['product_name'])->toBe('Organic Turmeric');
    expect($parsed->entities['max_budget'])->toBe(200.0);
});

test('admin ai status health page verifies all step 1-7 checks successfully', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get(route('admin.ai.index'));

    $response->assertOk();
    $response->assertSee('Language Detection Engine');
    $response->assertSee('Intent & Entity Extraction Service');
    $response->assertSee('Multilingual Response Generator');
    $response->assertSee('Multilingual Test Suite Matrix');
    $response->assertSee('✓ Verified');
});
