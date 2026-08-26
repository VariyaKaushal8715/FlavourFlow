<?php

namespace App\AI\Contracts;

use App\AI\Core\AiParsedIntent;

interface AiLanguageUnderstandingInterface
{
    /**
     * Detect language code of a user query.
     * Supported: 'gu' (Gujarati script), 'gujenglish' (Gujarati in Latin script), 'hi' (Hindi/Devanagari script), 'hinglish' (Hindi in Latin script), 'en' (English).
     */
    public function detectLanguage(string $query): string;

    /**
     * Parse intent, entities, budget, categories, and preferences from user query.
     *
     * @param  array<string, mixed>  $context
     */
    public function understand(string $query, array $context = []): AiParsedIntent;

    /**
     * Generate localized response text in the target language.
     *
     * @param  array<string, mixed>  $data
     */
    public function formatResponse(string $intent, string $language, array $data = []): string;

    /**
     * Get all supported language codes.
     *
     * @return list<string>
     */
    public function getSupportedLanguages(): array;
}
