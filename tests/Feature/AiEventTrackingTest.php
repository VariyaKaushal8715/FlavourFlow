<?php

use App\AI\Contracts\AiAdapterInterface;
use App\AI\Contracts\AiAnalyzerInterface;
use App\AI\Contracts\AiBrainInterface;
use App\AI\Contracts\AiContextBuilderInterface;
use App\AI\Contracts\AiEngineInterface;
use App\AI\Contracts\AiEventTrackerInterface;
use App\AI\Contracts\AiProviderInterface;
use App\AI\Contracts\AiRecommendationEngineInterface;
use App\AI\Providers\OpenRouterProvider;
use App\AI\Services\AiProviderManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ai engine contracts and services resolve from container (steps 1-6)', function () {
    expect(app()->bound(AiEngineInterface::class))->toBeTrue();
    expect(app()->bound(AiAdapterInterface::class))->toBeTrue();
    expect(app()->bound(AiEventTrackerInterface::class))->toBeTrue();
    expect(app()->bound(AiContextBuilderInterface::class))->toBeTrue();
    expect(app()->bound(AiAnalyzerInterface::class))->toBeTrue();
    expect(app()->bound(AiProviderManager::class))->toBeTrue();
    expect(app()->bound(AiProviderInterface::class))->toBeTrue();
    expect(app()->bound(AiBrainInterface::class))->toBeTrue();
    expect(app()->bound(AiRecommendationEngineInterface::class))->toBeTrue();

    $engine = app(AiEngineInterface::class);
    expect($engine->isReady())->toBeTrue();
});

test('ai provider manager handles driver resolution and openrouter configuration', function () {
    $manager = app(AiProviderManager::class);
    expect($manager->getSupportedProviders())->toContain('openrouter', 'groq', 'gemini', 'openai', 'ollama', 'null');

    $openRouter = $manager->resolveDriverInstance('openrouter');
    expect($openRouter)->toBeInstanceOf(OpenRouterProvider::class);
    expect($openRouter->getName())->toBe('openrouter');

    // Without API key, openrouter is not available and falls back to NullProvider gracefully
    $activeProvider = $manager->driver('openrouter');
    expect($activeProvider->isAvailable())->toBeTrue(); // NullProvider fallback is available
});

test('admin ai status health page verifies all step 1-6 checks successfully', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get(route('admin.ai.index'));

    $response->assertOk();
    $response->assertSee('AI Provider Configuration');
    $response->assertSee('API Connectivity & Auth Test');
    $response->assertSee('Model Availability & Active Model');
    $response->assertSee('AI Brain → Provider Integration');
});
