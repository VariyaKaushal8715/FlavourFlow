<?php

namespace App\AI\Providers;

use App\AI\Contracts\AiProviderInterface;

/**
 * Null provider used when no external LLM API key is configured.
 * Returns a structured passthrough response so the AI Engine operates deterministically.
 */
class NullProvider implements AiProviderInterface
{
    public function getName(): string
    {
        return 'null';
    }

    public function getModel(): string
    {
        return 'deterministic-v1';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function testConnection(): array
    {
        return [
            'success' => true,
            'message' => 'NullProvider active (Deterministic mode).',
            'details' => [
                'provider' => 'null',
                'mode' => 'deterministic',
            ],
        ];
    }

    public function generate(array $prompt): array
    {
        return [
            'content' => '',
            'tokens_used' => 0,
            'model' => 'deterministic-v1',
            'raw' => [
                'provider' => 'null',
                'note' => 'No external LLM connected. Fallback to deterministic AI reasoning.',
            ],
            'success' => true,
            'error' => null,
        ];
    }

    public function getSupportedLanguages(): array
    {
        return ['en', 'hi', 'gu', 'hinglish', 'gujenglish'];
    }
}
