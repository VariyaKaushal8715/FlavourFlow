<?php

namespace App\AI\Core;

use JsonSerializable;

class AiParsedIntent implements JsonSerializable
{
    /**
     * @param  string  $intent  Parsed intent (e.g. 'product_search', 'recommendation', 'cart_query', 'order_tracking', 'price_query', 'category_explore', 'general')
     * @param  string  $language  Language code ('gu', 'gujenglish', 'hi', 'hinglish', 'en')
     * @param  array{category: ?string, product_name: ?string, max_budget: ?float, keywords: list<string>, quantity: ?int}  $entities
     * @param  float  $confidence  Confidence score between 0.0 and 1.0
     * @param  string  $originalQuery  Original raw user input
     * @param  string  $normalizedQuery  Normalized & transliterated query
     */
    public function __construct(
        public readonly string $intent,
        public readonly string $language,
        public readonly array $entities,
        public readonly float $confidence,
        public readonly string $originalQuery,
        public readonly string $normalizedQuery,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'intent' => $this->intent,
            'language' => $this->language,
            'entities' => $this->entities,
            'confidence' => $this->confidence,
            'original_query' => $this->originalQuery,
            'normalized_query' => $this->normalizedQuery,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
