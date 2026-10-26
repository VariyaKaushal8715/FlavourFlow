<?php

use App\AI\Contracts\AiProviderInterface;
use App\AI\Providers\GeminiProvider;
use App\AI\Providers\GroqProvider;
use App\AI\Providers\NullProvider;
use App\AI\Providers\OpenRouterProvider;
use App\AI\Services\AiProviderManager;
use App\AI\Services\CustomerAiAssistantService;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('ai provider manager resolves all supported provider instances', function () {
    /** @var AiProviderManager $manager */
    $manager = app(AiProviderManager::class);

    expect($manager->getSupportedProviders())->toContain('openrouter', 'groq', 'gemini', 'null');

    $nullInstance = $manager->resolveDriverInstance('null');
    expect($nullInstance)->toBeInstanceOf(NullProvider::class)
        ->and($nullInstance->getName())->toBe('null')
        ->and($nullInstance->isAvailable())->toBeTrue();

    $groqInstance = $manager->resolveDriverInstance('groq');
    expect($groqInstance)->toBeInstanceOf(GroqProvider::class)
        ->and($groqInstance->getName())->toBe('groq');

    $geminiInstance = $manager->resolveDriverInstance('gemini');
    expect($geminiInstance)->toBeInstanceOf(GeminiProvider::class)
        ->and($geminiInstance->getName())->toBe('gemini');

    $openRouterInstance = $manager->resolveDriverInstance('openrouter');
    expect($openRouterInstance)->toBeInstanceOf(OpenRouterProvider::class)
        ->and($openRouterInstance->getName())->toBe('openrouter');
});

test('provider manager falls back cleanly to NullProvider when requested provider is unavailable', function () {
    /** @var AiProviderManager $manager */
    $manager = app(AiProviderManager::class);

    // Groq with no key in test environment falls back to NullProvider
    config(['ai.providers.groq.api_key' => '']);
    $driver = $manager->driver('groq');

    expect($driver)->toBeInstanceOf(NullProvider::class)
        ->and($driver->getName())->toBe('null')
        ->and($driver->isAvailable())->toBeTrue();
});

test('NullProvider returns deterministic response and passes connectivity check', function () {
    $provider = new NullProvider;

    $testResult = $provider->testConnection();
    expect($testResult['success'])->toBeTrue()
        ->and($testResult['message'])->toContain('NullProvider active');

    $genResult = $provider->generate([
        'system' => 'test system',
        'user' => 'test user',
    ]);
    expect($genResult['success'])->toBeTrue()
        ->and($genResult['model'])->toBe('deterministic-v1');
});

test('Groq and Gemini providers handle unconfigured states safely without throwing', function () {
    $groq = new GroqProvider(['api_key' => '']);
    expect($groq->isAvailable())->toBeFalse();

    $groqTest = $groq->testConnection();
    expect($groqTest['success'])->toBeFalse()
        ->and($groqTest['message'])->toContain('GROQ_API_KEY');

    $groqGen = $groq->generate(['system' => 'sys', 'user' => 'hi']);
    expect($groqGen['success'])->toBeFalse()
        ->and($groqGen['error'])->toContain('Groq API Key not configured');

    $gemini = new GeminiProvider(['api_key' => '']);
    expect($gemini->isAvailable())->toBeFalse();

    $geminiTest = $gemini->testConnection();
    expect($geminiTest['success'])->toBeFalse()
        ->and($geminiTest['message'])->toContain('GEMINI_API_KEY');

    $geminiGen = $gemini->generate(['system' => 'sys', 'user' => 'hi']);
    expect($geminiGen['success'])->toBeFalse()
        ->and($geminiGen['error'])->toContain('Gemini API Key not configured');
});

test('providers handle network exceptions gracefully without leaking secrets', function () {
    Http::fake([
        'https://api.groq.com/openai/v1/models' => Http::response(['error' => ['message' => 'Invalid API Key']], 401),
        'https://generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'API_KEY_INVALID']], 400),
    ]);

    $fakeGroqKey = 'gsk_super_secret_test_key_12345';
    $groq = new GroqProvider(['api_key' => $fakeGroqKey]);
    $groqTest = $groq->testConnection();

    expect($groqTest['success'])->toBeFalse()
        ->and($groqTest['message'])->not->toContain($fakeGroqKey);

    $fakeGeminiKey = 'AIzaSy_super_secret_gemini_key_67890';
    $gemini = new GeminiProvider(['api_key' => $fakeGeminiKey]);
    $geminiTest = $gemini->testConnection();

    expect($geminiTest['success'])->toBeFalse()
        ->and($geminiTest['message'])->not->toContain($fakeGeminiKey);
});

test('admin provider test endpoint requires admin authentication', function () {
    // Guest access
    $guestRes = $this->postJson(route('admin.ai.providerTest'), [
        'provider' => 'null',
    ]);
    expect($guestRes->status())->toBeIn([401, 403, 302]);

    // Regular non-admin customer
    $user = User::factory()->create();
    $userRes = $this->actingAs($user)->postJson(route('admin.ai.providerTest'), [
        'provider' => 'null',
    ]);
    $userRes->assertForbidden();
});

test('admin can safely test provider connectivity via dedicated endpoint', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->postJson(route('admin.ai.providerTest'), [
        'provider' => 'null',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'provider',
            'success',
            'message',
            'details',
        ]);

    expect($response['provider'])->toBe('null')
        ->and($response['success'])->toBeTrue();
});

test('customer assistant utilizes provider reasoning when mocked and falls back cleanly on error', function () {
    Product::factory()->create([
        'name' => 'Kashmiri Saffron',
        'category' => 'Whole Spices',
        'price' => 450.00,
        'quantity' => 10,
        'is_active' => true,
    ]);

    // 1. Mocking a working provider
    $mockProvider = Mockery::mock(AiProviderInterface::class);
    $mockProvider->shouldReceive('getName')->andReturn('mock_llm');
    $mockProvider->shouldReceive('isAvailable')->andReturn(true);
    $mockProvider->shouldReceive('generate')->once()->andReturn([
        'content' => 'Kashmiri Saffron is our most prized spice, sourced straight from Pampore for ₹450.00.',
        'tokens_used' => 25,
        'model' => 'mock-model',
        'raw' => [],
        'success' => true,
        'error' => null,
    ]);

    // Force live test flag for this specific mock test
    config(['ai.force_live_tests' => true]);

    /** @var CustomerAiAssistantService $service */
    $service = app(CustomerAiAssistantService::class);
    // Inject mocked provider
    $ref = new ReflectionClass($service);
    $prop = $ref->getProperty('provider');
    $prop->setValue($service, $mockProvider);

    $res = $service->processMessage('Tell me about Kashmiri Saffron');
    expect($res['message'])->toBe('Kashmiri Saffron is our most prized spice, sourced straight from Pampore for ₹450.00.')
        ->and($res['products'])->toHaveCount(1);

    // 2. Provider fails or errors -> fallback cleanly to grounded deterministic template
    $mockFailingProvider = Mockery::mock(AiProviderInterface::class);
    $mockFailingProvider->shouldReceive('getName')->andReturn('failing_llm');
    $mockFailingProvider->shouldReceive('isAvailable')->andReturn(true);
    $mockFailingProvider->shouldReceive('generate')->once()->andReturn([
        'content' => '',
        'tokens_used' => 0,
        'model' => 'failing-model',
        'raw' => [],
        'success' => false,
        'error' => 'Rate limit exceeded',
    ]);

    $prop->setValue($service, $mockFailingProvider);
    $fallbackRes = $service->processMessage('What is the price of Kashmiri Saffron?');

    // Should not throw or crash; falls back to deterministic formatted string containing real price
    expect($fallbackRes['message'])->toContain('450.00');

    config(['ai.force_live_tests' => false]);
});
