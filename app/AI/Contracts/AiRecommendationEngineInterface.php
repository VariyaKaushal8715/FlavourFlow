<?php

namespace App\AI\Contracts;

use App\AI\Core\AiDecisionResult;
use App\AI\Core\AiRecommendationResult;

interface AiRecommendationEngineInterface
{
    /**
     * Generate customer recommendations based on context and type.
     *
     * Types: 'personalized', 'complementary', 'cart_wishlist', 'recently_viewed', 'category_based', 'abandoned_cart_recovery'
     */
    public function getCustomerRecommendations(?int $userId = null, ?string $sessionId = null, string $type = 'personalized', int $limit = 6): AiRecommendationResult;

    /**
     * Generate admin decision signals and strategic recommendations.
     */
    public function getAdminDecisionSignals(int $days = 30): AiDecisionResult;
}
