<?php

namespace App\AI\Contracts;

use App\AI\Core\AiReasoningResponse;

interface AiBrainInterface
{
    /**
     * Perform reasoning over structured context for a given intent.
     *
     * @param  array<string, mixed>  $context  Structured context from AiContextBuilderInterface.
     * @param  array<string, mixed>  $options  Additional options (language, audience, etc).
     */
    public function reason(string $intent, array $context, array $options = []): AiReasoningResponse;

    /**
     * Generate customer-facing reasoning (product recommendations, answers, suggestions).
     *
     * @param  array<string, mixed>  $context
     */
    public function reasonForCustomer(array $context, string $query, string $language = 'en'): AiReasoningResponse;

    /**
     * Generate admin-facing reasoning (analytics insights, business intelligence).
     *
     * @param  array<string, mixed>  $context
     */
    public function reasonForAdmin(array $context, string $query): AiReasoningResponse;

    /**
     * Get all languages supported for reasoning output.
     *
     * @return list<string>
     */
    public function getSupportedLanguages(): array;

    /**
     * Check if the AI Brain is fully initialized and ready for reasoning.
     */
    public function isReady(): bool;
}
