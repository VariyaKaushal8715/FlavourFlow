<?php

namespace App\AI\Core;

use JsonSerializable;

class AiReasoningResponse implements JsonSerializable
{
    /**
     * @param  string  $intent  The detected or requested intent (e.g. 'product_recommendation', 'order_help').
     * @param  string  $reasoning  Human-readable reasoning text.
     * @param  float  $confidence  Confidence score between 0.0 and 1.0.
     * @param  list<array{action: string, label: string, data: array<string, mixed>}>  $recommendedActions  Actionable next-steps.
     * @param  array<string, mixed>  $data  Arbitrary structured payload (product IDs, insights, etc).
     * @param  string  $language  Language code of the response.
     * @param  string  $audience  Target audience ('customer' or 'admin').
     * @param  string  $provider  Provider that generated this response.
     */
    public function __construct(
        public readonly string $intent,
        public readonly string $reasoning,
        public readonly float $confidence,
        public readonly array $recommendedActions = [],
        public readonly array $data = [],
        public readonly string $language = 'en',
        public readonly string $audience = 'customer',
        public readonly string $provider = 'deterministic',
    ) {}

    public function isHighConfidence(): bool
    {
        return $this->confidence >= 0.7;
    }

    public function isMediumConfidence(): bool
    {
        return $this->confidence >= 0.4 && $this->confidence < 0.7;
    }

    public function isLowConfidence(): bool
    {
        return $this->confidence < 0.4;
    }

    public function getConfidenceLabel(): string
    {
        return match (true) {
            $this->confidence >= 0.7 => 'high',
            $this->confidence >= 0.4 => 'medium',
            default => 'low',
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'intent' => $this->intent,
            'reasoning' => $this->reasoning,
            'confidence' => $this->confidence,
            'confidence_label' => $this->getConfidenceLabel(),
            'recommended_actions' => $this->recommendedActions,
            'data' => $this->data,
            'language' => $this->language,
            'audience' => $this->audience,
            'provider' => $this->provider,
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
