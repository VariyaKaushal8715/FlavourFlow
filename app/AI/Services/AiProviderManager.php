<?php

namespace App\AI\Services;

use App\AI\Contracts\AiProviderInterface;
use App\AI\Providers\NullProvider;
use App\AI\Providers\OpenRouterProvider;
use InvalidArgumentException;

class AiProviderManager
{
    /**
     * Instantiated provider drivers cache.
     *
     * @var array<string, AiProviderInterface>
     */
    private array $drivers = [];

    /**
     * Get a provider driver instance by name.
     */
    public function driver(?string $name = null): AiProviderInterface
    {
        $driverName = $name ?? (string) config('ai.default_provider', 'openrouter');

        if (isset($this->drivers[$driverName])) {
            return $this->resolveAvailableDriver($this->drivers[$driverName]);
        }

        $provider = match ($driverName) {
            'openrouter' => new OpenRouterProvider(config('ai.providers.openrouter', [])),
            'null' => new NullProvider,
            'groq', 'gemini', 'openai', 'ollama' => $this->createPlaceholderDriver($driverName),
            default => throw new InvalidArgumentException("Unsupported AI provider driver [{$driverName}]."),
        };

        $this->drivers[$driverName] = $provider;

        return $this->resolveAvailableDriver($provider);
    }

    /**
     * Get all supported provider names.
     *
     * @return list<string>
     */
    public function getSupportedProviders(): array
    {
        return ['openrouter', 'groq', 'gemini', 'openai', 'ollama', 'null'];
    }

    /**
     * Get active driver status breakdown.
     *
     * @return array<string, array{name: string, model: string, available: bool}>
     */
    public function getProvidersStatus(): array
    {
        $status = [];
        foreach ($this->getSupportedProviders() as $name) {
            try {
                $instance = $this->resolveDriverInstance($name);
                $status[$name] = [
                    'name' => $instance->getName(),
                    'model' => $instance->getModel(),
                    'available' => $instance->isAvailable(),
                ];
            } catch (\Throwable) {
                $status[$name] = [
                    'name' => $name,
                    'model' => 'unsupported',
                    'available' => false,
                ];
            }
        }

        return $status;
    }

    /**
     * If requested provider is unavailable (e.g. missing API key), fallback to NullProvider.
     */
    private function resolveAvailableDriver(AiProviderInterface $provider): AiProviderInterface
    {
        if (! $provider->isAvailable() && $provider->getName() !== 'null') {
            return new NullProvider;
        }

        return $provider;
    }

    /**
     * Create driver instance without fallback.
     */
    public function resolveDriverInstance(string $name): AiProviderInterface
    {
        return match ($name) {
            'openrouter' => new OpenRouterProvider(config('ai.providers.openrouter', [])),
            'null' => new NullProvider,
            default => $this->createPlaceholderDriver($name),
        };
    }

    private function createPlaceholderDriver(string $name): AiProviderInterface
    {
        return new class($name) implements AiProviderInterface
        {
            public function __construct(private string $name) {}

            public function getName(): string
            {
                return $this->name;
            }

            public function getModel(): string
            {
                return "{$this->name}-default";
            }

            public function isAvailable(): bool
            {
                return false;
            }

            public function testConnection(): array
            {
                return [
                    'success' => false,
                    'message' => "Provider [{$this->name}] architecture ready. Configuration pending.",
                    'details' => ['provider' => $this->name],
                ];
            }

            public function generate(array $prompt): array
            {
                return [
                    'content' => '',
                    'tokens_used' => 0,
                    'model' => $this->getModel(),
                    'raw' => [],
                    'success' => false,
                    'error' => "Provider [{$this->name}] not configured yet.",
                ];
            }

            public function getSupportedLanguages(): array
            {
                return ['en', 'hi', 'gu', 'hinglish', 'gujenglish'];
            }
        };
    }
}
