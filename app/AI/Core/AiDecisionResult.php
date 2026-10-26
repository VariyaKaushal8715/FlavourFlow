<?php

namespace App\AI\Core;

use JsonSerializable;

class AiDecisionResult implements JsonSerializable
{
    /**
     * @param  list<array{product_id: string, name: string, score: int, status: string, reason: string}>  $winningProducts
     * @param  list<array{product_id: string, name: string, stock: int, reason: string, suggested_action: string}>  $promotionNeeds
     * @param  list<array{category: string, reason: string, demand_level: string}>  $categoryOpportunities
     * @param  array{abandoned_count: int, risk_level: string, reason: string, action: string}  $abandonmentSignals
     * @param  list<array{offer_name: string, target_category: string, discount_type: string, reason: string}>  $offerOpportunities
     * @param  list<array{product_id: string, name: string, campaign_angle: string, target_audience: string}>  $adCampaignSuggestions
     * @param  list<array{title: string, description: string, priority: string, action_type: string}>  $suggestedActions
     */
    public function __construct(
        public readonly string $decisionType = 'admin_business_decisions',
        public readonly array $winningProducts = [],
        public readonly array $promotionNeeds = [],
        public readonly array $categoryOpportunities = [],
        public readonly array $abandonmentSignals = [],
        public readonly array $offerOpportunities = [],
        public readonly array $adCampaignSuggestions = [],
        public readonly array $suggestedActions = [],
        public readonly float $confidence = 0.0,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'decision_type' => $this->decisionType,
            'winning_products' => $this->winningProducts,
            'promotion_needs' => $this->promotionNeeds,
            'category_opportunities' => $this->categoryOpportunities,
            'abandonment_signals' => $this->abandonmentSignals,
            'offer_opportunities' => $this->offerOpportunities,
            'ad_campaign_suggestions' => $this->adCampaignSuggestions,
            'suggested_actions' => $this->suggestedActions,
            'confidence' => $this->confidence,
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
