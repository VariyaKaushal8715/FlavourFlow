<?php

namespace App\AI\Services;

use App\AI\Contracts\AiAnalyzerInterface;
use App\AI\Contracts\AiBrainInterface;
use App\AI\Contracts\AiContextBuilderInterface;
use App\AI\Contracts\AiLanguageUnderstandingInterface;
use App\AI\Contracts\AiRecommendationEngineInterface;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class CustomerAiAssistantService
{
    public function __construct(
        private AiLanguageUnderstandingInterface $nlu,
        private AiContextBuilderInterface $contextBuilder,
        private AiAnalyzerInterface $analyzer,
        private AiBrainInterface $brain,
        private AiRecommendationEngineInterface $recEngine,
    ) {}

    /**
     * Process a customer chat message and generate a rich AI response.
     *
     * @return array{
     *     id: string,
     *     sender: string,
     *     message: string,
     *     language: string,
     *     intent: string,
     *     products: list<array<string, mixed>>,
     *     actions: list<array<string, mixed>>,
     *     timestamp: string
     * }
     */
    public function processMessage(string $message, ?int $userId = null, ?string $sessionId = null): array
    {
        try {
            $effectiveUserId = $userId ?? (auth()->check() ? auth()->id() : null);
            $effectiveSessionId = $sessionId ?? (session()->isStarted() ? session()->getId() : 'guest-'.Str::random(8));

            // 1. Understand input query & language
            $parsedIntent = $this->nlu->understand($message);
            $language = $parsedIntent->language;
            $intent = $parsedIntent->intent;
            $entities = $parsedIntent->entities;

            // 2. Fetch context & analysis
            $context = $this->contextBuilder->buildContext($effectiveUserId, $effectiveSessionId);
            $analysis = $this->analyzer->analyze($context);

            // 3. Obtain real DB products based on extracted intent & entities
            $products = $this->findRealProducts($intent, $entities, $analysis);

            // 4. Generate Brain Reasoning
            $brainResponse = $this->brain->reasonForCustomer($context, $message, $language);

            // 5. Build localized message response text
            $responseText = $this->buildResponseText($message, $parsedIntent, $brainResponse->reasoning, $products, $language);

            // 6. Format rich product cards
            $productCards = $this->formatProductCards($products, $parsedIntent);

            // 7. Recommended actions
            $actions = $this->buildRecommendedActions($intent, $products, $language);

            $responsePayload = [
                'id' => (string) Str::uuid(),
                'sender' => 'assistant',
                'message' => $responseText,
                'language' => $language,
                'intent' => $intent,
                'products' => $productCards,
                'actions' => $actions,
                'timestamp' => now()->toIso8601String(),
            ];

            // Record conversation turn in session memory
            $this->storeInSessionHistory($message, $responsePayload);

            return $responsePayload;
        } catch (Throwable $e) {
            logger()->error('Customer AI Assistant Exception: '.$e->getMessage());

            return [
                'id' => (string) Str::uuid(),
                'sender' => 'assistant',
                'message' => 'I am sorry, I ran into an error processing your request. Please try asking again!',
                'language' => 'en',
                'intent' => 'error',
                'products' => [],
                'actions' => [],
                'timestamp' => now()->toIso8601String(),
            ];
        }
    }

    /**
     * Get recent session chat history.
     *
     * @return list<array<string, mixed>>
     */
    public function getHistory(): array
    {
        return session()->get('ai_chat_history', []);
    }

    /**
     * Clear active session conversation history.
     */
    public function clearHistory(): void
    {
        session()->forget('ai_chat_history');
    }

    /**
     * Find real database products matching parsed entities or recommendations.
     *
     * @param  array{category: ?string, product_name: ?string, max_budget: ?float, keywords: list<string>}  $entities
     * @return Collection<int, Product>
     */
    private function findRealProducts(string $intent, array $entities, array $analysis): Collection
    {
        $query = Product::query()->active();

        // Specific product entity matched
        if (! empty($entities['product_name'])) {
            $matched = (clone $query)->where('name', 'like', "%{$entities['product_name']}%")->get();
            if ($matched->isNotEmpty()) {
                return $matched->take(4);
            }
        }

        // Category matched
        if (! empty($entities['category'])) {
            $query->where('category', $entities['category']);
        }

        // Max budget constraint matched
        if (! empty($entities['max_budget']) && $entities['max_budget'] > 0) {
            $query->where('price', '<=', $entities['max_budget']);
        }

        // Keywords matched
        if (! empty($entities['keywords']) && empty($entities['category']) && empty($entities['product_name'])) {
            $query->where(function ($q) use ($entities): void {
                foreach ($entities['keywords'] as $kw) {
                    $q->orWhere('name', 'like', "%{$kw}%")
                        ->orWhere('description', 'like', "%{$kw}%")
                        ->orWhere('category', 'like', "%{$kw}%");
                }
            });
        }

        $results = $query->orderByDesc('is_featured')->orderByDesc('priority')->take(4)->get();

        // If no items match query constraints, fallback to top active products
        if ($results->isEmpty()) {
            return Product::query()->active()->orderByDesc('priority')->take(4)->get();
        }

        return $results;
    }

    /**
     * Build natural response text in target language.
     *
     * @param  Collection<int, Product>  $products
     */
    private function buildResponseText(string $query, $parsedIntent, string $brainReasoning, Collection $products, string $language): string
    {
        $intent = $parsedIntent->intent;

        if ($intent === 'order_tracking') {
            return $this->nlu->formatResponse('order_tracking', $language);
        }

        if ($intent === 'cart_query') {
            return $this->nlu->formatResponse('cart_query', $language);
        }

        $baseText = $this->nlu->formatResponse($intent, $language, [
            'product_name' => $products->first()?->name ?? 'spices',
        ]);

        if ($products->isNotEmpty()) {
            $count = $products->count();
            $text = "{$baseText} ({$count} item(s) found)";

            if ($brainReasoning !== '' && ! str_contains($brainReasoning, 'Unable to process')) {
                $text .= "\n\n".$brainReasoning;
            }

            return $text;
        }

        return $baseText;
    }

    /**
     * Format rich product cards for chat UI.
     *
     * @param  Collection<int, Product>  $products
     * @return list<array<string, mixed>>
     */
    private function formatProductCards(Collection $products, $parsedIntent): array
    {
        $cards = [];

        foreach ($products as $p) {
            $reason = "Matches your request for {$p->category}.";
            if ($parsedIntent->entities['max_budget']) {
                $reason .= " Fits within your ₹{$parsedIntent->entities['max_budget']} budget.";
            }

            $cards[] = [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => (float) $p->price,
                'formatted_price' => '₹'.number_format((float) $p->price, 2),
                'category' => $p->category,
                'image_url' => asset($p->image ?? 'images/flavourflow-mark.png'),
                'is_active' => (bool) $p->is_active,
                'in_stock' => $p->quantity > 0,
                'stock_quantity' => $p->quantity,
                'reason' => $reason,
                'url' => route('products.show', ['product' => $p->slug]),
                'store_cart_url' => route('cart.store', ['product' => $p->slug]),
                'wishlist_store_url' => route('wishlist.store', ['product' => $p->slug]),
            ];
        }

        return $cards;
    }

    /**
     * Build interactive action buttons.
     *
     * @param  Collection<int, Product>  $products
     * @return list<array{action: string, label: string, url: string}>
     */
    private function buildRecommendedActions(string $intent, Collection $products, string $language): array
    {
        $actions = [];

        if ($intent === 'order_tracking') {
            $actions[] = [
                'action' => 'view_orders',
                'label' => 'View My Orders',
                'url' => route('account.orders'),
            ];
        }

        if ($intent === 'cart_query') {
            $actions[] = [
                'action' => 'view_cart',
                'label' => 'Go to Cart',
                'url' => route('cart.index'),
            ];
        }

        if ($products->isNotEmpty()) {
            $actions[] = [
                'action' => 'view_cart',
                'label' => 'View Shopping Cart',
                'url' => route('cart.index'),
            ];
        }

        return $actions;
    }

    /**
     * Append message turn to session memory.
     */
    private function storeInSessionHistory(string $userMessage, array $responsePayload): void
    {
        $history = session()->get('ai_chat_history', []);

        $history[] = [
            'id' => (string) Str::uuid(),
            'sender' => 'user',
            'message' => $userMessage,
            'timestamp' => now()->toIso8601String(),
        ];

        $history[] = $responsePayload;

        // Keep last 30 turns in session memory
        if (count($history) > 30) {
            $history = array_slice($history, -30);
        }

        session()->put('ai_chat_history', $history);
    }
}
