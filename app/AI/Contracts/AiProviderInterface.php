<?php

namespace App\AI\Contracts;

interface AiProviderInterface
{
    /**
     * Get the provider name identifier (e.g. 'openrouter', 'groq', 'gemini', 'openai', 'ollama', 'null').
     */
    public function getName(): string;

    /**
     * Get the active model identifier.
     */
    public function getModel(): string;

    /**
     * Check if this provider is configured and available for use.
     */
    public function isAvailable(): bool;

    /**
     * Test connection to provider API.
     *
     * @return array{success: bool, message: string, details: array<string, mixed>}
     */
    public function testConnection(): array;

    /**
     * Generate a response from the provider given a structured prompt.
     *
     * @param  array{system: string, user: string, context?: array<string, mixed>, language?: string}  $prompt
     * @return array{content: string, tokens_used: int, model: string, raw: array<string, mixed>, success: bool, error: ?string}
     */
    public function generate(array $prompt): array;

    /**
     * Get languages this provider supports.
     *
     * @return list<string>
     */
    public function getSupportedLanguages(): array;
}
