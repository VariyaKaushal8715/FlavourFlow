<?php

namespace App\AI\Contracts;

interface AiProviderInterface
{
    /**
     * Get the provider name identifier.
     */
    public function getName(): string;

    /**
     * Check if this provider is configured and available for use.
     */
    public function isAvailable(): bool;

    /**
     * Generate a response from the provider given a structured prompt.
     *
     * @param  array{system: string, user: string, context: array<string, mixed>, language: string}  $prompt
     * @return array{content: string, tokens_used: int, model: string, raw: array<string, mixed>}
     */
    public function generate(array $prompt): array;

    /**
     * Get languages this provider supports.
     *
     * @return list<string>
     */
    public function getSupportedLanguages(): array;
}
