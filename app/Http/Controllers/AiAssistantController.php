<?php

namespace App\Http\Controllers;

use App\AI\Contracts\AiBrainInterface;
use App\AI\Contracts\AiRecommendationEngineInterface;
use App\AI\Services\CustomerAiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function __construct(private CustomerAiAssistantService $assistant) {}

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        $message = trim($validated['message']);

        $response = $this->assistant->processMessage(
            message: $message,
            userId: $request->user()?->id,
            sessionId: session()->getId()
        );

        return response()->json([
            'success' => true,
            'response' => $response,
            'provider_available' => app(AiBrainInterface::class)->isReady(),
        ]);
    }

    public function history(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'history' => $this->assistant->getHistory(),
            'provider_available' => app(AiBrainInterface::class)->isReady(),
        ]);
    }

    public function clear(): JsonResponse
    {
        $this->assistant->clearHistory();

        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared.',
            'provider_available' => app(AiBrainInterface::class)->isReady(),
        ]);
    }

    public function recommendations(Request $request, AiRecommendationEngineInterface $recEngine): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:personalized,complementary,cart_wishlist,recently_viewed,category_based,abandoned_cart_recovery'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:12'],
            'product_id' => ['nullable', 'string', 'max:50'],
        ]);

        $type = $validated['type'] ?? 'personalized';
        $limit = isset($validated['limit']) ? (int) $validated['limit'] : 6;
        $productId = $validated['product_id'] ?? null;

        $result = $recEngine->getCustomerRecommendations(
            userId: $request->user()?->id,
            sessionId: session()->getId(),
            type: $type,
            limit: $limit,
            contextOptions: $productId !== null ? ['product_id' => $productId] : []
        );

        return response()->json([
            'success' => true,
            'recommendation_type' => $result->recommendationType,
            'reason' => $result->reason,
            'confidence' => $result->confidence,
            'products' => $result->products,
            'suggested_action' => $result->suggestedAction,
            'provider_available' => app(AiBrainInterface::class)->isReady(),
        ]);
    }
}
