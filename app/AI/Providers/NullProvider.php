<?php

namespace App\AI\Providers;

use App\AI\Contracts\AiProviderInterface;

/**
 * Null provider used before any real LLM API is connected.
 *
 * Returns a structured passthrough response so the Brain can still
 * reason deterministically without any external dependency.
 */
class NullProvider implements AiProviderInterface
{
    public function getName(): string
    {
        return 'null';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function generate(array $prompt): array
    {
        return [
            'content' => '',
            'tokens_used' => 0,
            'model' => 'deterministic-v1',
            'raw' => [
                'provider' => 'null',
                'note' => 'No external LLM connected. Reasoning is fully deterministic.',
            ],
        ];
    }

    public function getSupportedLanguages(): array
    {
        return ['en', 'hi', 'gu', 'hinglish', 'gujenglish'];
    }
}
