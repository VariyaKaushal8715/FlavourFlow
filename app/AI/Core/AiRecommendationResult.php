<?php

namespace App\AI\Core;

use JsonSerializable;

class AiRecommendationResult implements JsonSerializable
{
    /**
     * @param  string  $recommendationType  e.g. 'personalized', 'complementary', 'abandoned_cart_recovery'
     * @param  list<array{product_id: string, name: string, slug: string, price: float, category: string, reason: string, confidence: float, suggested_action: array<string, mixed>}>  $products
     * @param  string  $reason  Overall reason explaining why these recommendations were selected
     * @param  float  $confidence  Confidence score between 0.0 and 1.0
     * @param  array<string, mixed>  $suggestedAction  Overall recommended action for the UI
     * @param  array<string, mixed>  $metadata  Additional metadata
     */
    public function __construct(
        public readonly string $recommendationType,
        public readonly array $products = [],
        public readonly string $reason = '',
        public readonly float $confidence = 0.0,
        public readonly array $suggestedAction = [],
        public readonly array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'recommendation_type' => $this->recommendationType,
            'products' => $this->products,
            'reason' => $this->reason,
            'confidence' => $this->confidence,
            'suggested_action' => $this->suggestedAction,
            'metadata' => $this->metadata,
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
