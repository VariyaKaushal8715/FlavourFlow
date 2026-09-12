<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotService
{
    protected ?string $apiKey;

    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Process user chat message securely.
     */
    public function reply(string $userMessage, ?int $userId = null): array
    {
        $cleanQuery = trim($userMessage);
        if ($cleanQuery === '') {
            return [
                'reply' => 'Please type a question or product request.',
                'products' => [],
                'orders' => [],
            ];
        }

        // 1. DELIVERY_CHARGES INTENT
        if ($this->isDeliveryChargeQuery($cleanQuery)) {
            return [
                'reply' => 'Delivery is FREE on all orders of Rs. 500 or above! For orders under Rs. 500, a flat delivery charge of Rs. 50 applies.',
                'products' => [],
                'orders' => [],
            ];
        }

        // Fetch controlled contexts securely from database
        $productContext = $this->resolveProductContext($cleanQuery);
        $orderContext = $this->resolveOrderContext($cleanQuery, $userId);

        // System prompt and context assembly
        $systemPrompt = "You are FlavourFlow's AI Assistant, a friendly and helpful e-commerce customer support chatbot.\n\n"
            ."INTENT RULES:\n"
            ."1. DELIVERY CHARGES: If customer asks about shipping or delivery charges/fees, inform them delivery is FREE on orders of Rs. 500 and above, else Rs. 50. Do not mention user orders.\n"
            ."2. ORDER STATUS: Only consider active orders (Pending, Confirmed, Shipped, Out for Delivery). Ignore Delivered or Cancelled orders.\n"
            ."   - If user is not logged in, ask them to log in.\n"
            ."   - If user asks about a specific order number, show that order's status.\n"
            ."   - If multiple active orders exist, ask the user to choose which order to check and list options (e.g. '1. #ORD-XXX — Shipped'). Do NOT choose automatically.\n"
            ."   - If 0 active orders exist, reply: 'You don't have any active orders to track.'\n"
            ."3. PRODUCT RECOMMENDATIONS: Use Store Data for prices, stock, and descriptions.\n\n"
            ."STORE DATA:\n"
            ."Available Products matching query:\n".json_encode($productContext['data'], JSON_PRETTY_PRINT)."\n\n"
            ."Customer Active Orders:\n".json_encode($orderContext['data'], JSON_PRETTY_PRINT);

        // Attempt API call to Gemini
        if ($this->apiKey) {
            try {
                $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
                $response = Http::timeout(10)->post($endpoint, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $systemPrompt."\n\nUser Question: ".$cleanQuery],
                            ],
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $aiText = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($aiText) {
                        return [
                            'reply' => trim($aiText),
                            'products' => $productContext['models'],
                            'orders' => $orderContext['models'],
                        ];
                    }
                } else {
                    Log::warning('Gemini Chatbot API response error: '.$response->body());
                }
            } catch (\Throwable $e) {
                Log::error('Gemini Chatbot API Exception: '.$e->getMessage());
            }
        }

        // Rule-based fallback if API key is not present or API call fails
        $fallbackReply = $this->buildFallbackReply($cleanQuery, $productContext['data'], $orderContext, $userId);

        return [
            'reply' => $fallbackReply,
            'products' => $productContext['models'],
            'orders' => $orderContext['models'],
        ];
    }

    /**
     * Check if query is asking about delivery/shipping charges.
     */
    protected function isDeliveryChargeQuery(string $query): bool
    {
        $queryLower = Str::lower($query);
        $keywords = [
            'delivery charge', 'delivery charges', 'delivery fee', 'delivery fees',
            'shipping fee', 'shipping fees', 'shipping charge', 'shipping charges',
            'shipping cost', 'delivery cost', 'free delivery', 'delivery price',
            'shipping price', 'shipping rate', 'delivery rate', 'charges on product',
        ];

        foreach ($keywords as $kw) {
            if (Str::contains($queryLower, $kw)) {
                return true;
            }
        }

        return (bool) preg_match('/(?:delivery|shipping).*(?:charge|fee|cost|price|rate)|(?:charge|fee|cost|price|rate).*(?:delivery|shipping)/i', $queryLower);
    }

    /**
     * Controlled product context lookup (search, price under/over, active only).
     */
    protected function resolveProductContext(string $query): array
    {
        $queryLower = Str::lower($query);
        $productsQuery = Product::query()->where('is_active', true);

        // Check for budget constraints like "under 300" or "under Rs 300" or "below 200"
        if (preg_match('/(?:under|below|less than|\<)\s*(?:rs\.?|inr)?\s*(\d+)/i', $queryLower, $matches)) {
            $maxPrice = (float) $matches[1];
            $productsQuery->where('price', '<=', $maxPrice);
        }

        // Check for specific search terms
        $searchTerms = array_filter(explode(' ', preg_replace('/[^a-z0-9 ]/', '', $queryLower)));
        $ignoredWords = ['suggest', 'something', 'under', 'below', 'show', 'me', 'the', 'price', 'product', 'item', 'order', 'status', 'is', 'for', 'a', 'rs', 'inr'];
        $filteredTerms = array_diff($searchTerms, $ignoredWords);

        if (! empty($filteredTerms)) {
            $productsQuery->where(function ($q) use ($filteredTerms) {
                foreach ($filteredTerms as $term) {
                    $q->orWhere('name', 'like', "%{$term}%")
                        ->orWhere('category', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                }
            });
        }

        $matchedProducts = $productsQuery->orderBy('priority', 'asc')->limit(6)->get();

        // If specific search returned empty, fall back to top active featured products under maxPrice (if set)
        if ($matchedProducts->isEmpty()) {
            $fallbackQuery = Product::query()->where('is_active', true);
            if (isset($maxPrice)) {
                $fallbackQuery->where('price', '<=', $maxPrice);
            }
            $matchedProducts = $fallbackQuery->orderBy('priority', 'asc')->limit(5)->get();
        }

        $data = $matchedProducts->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'category' => $p->categoryName(),
            'price' => 'Rs. '.number_format((float) $p->price, 2),
            'stock_status' => $p->stockLabel(),
            'description' => Str::limit($p->description, 100),
            'url' => route('products.show', $p->slug),
        ])->toArray();

        return [
            'data' => $data,
            'models' => $matchedProducts,
        ];
    }

    /**
     * Controlled order context lookup (strictly scoped to authenticated user_id).
     */
    protected function resolveOrderContext(string $query, ?int $userId): array
    {
        if (! $userId) {
            return [
                'auth_required' => true,
                'data' => [],
                'models' => [],
                'is_specific' => false,
                'active_count' => 0,
            ];
        }

        $hasSpecificOrder = preg_match('/(ORD-[\w\-]+)/i', $query, $matches);

        if ($hasSpecificOrder) {
            $orderNumber = strtoupper($matches[1]);
            $orders = Order::query()
                ->where('user_id', $userId)
                ->where('order_number', $orderNumber)
                ->with('items')
                ->get();

            $data = $this->formatOrdersData($orders);

            return [
                'auth_required' => false,
                'data' => $data,
                'models' => $orders,
                'is_specific' => true,
                'active_count' => $orders->count(),
            ];
        }

        // Active orders only: exclude Delivered, Cancelled
        $activeStatuses = ['Pending', 'Confirmed', 'Shipped', 'Out for Delivery'];

        $orders = Order::query()
            ->where('user_id', $userId)
            ->whereIn('status', $activeStatuses)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $this->formatOrdersData($orders);

        return [
            'auth_required' => false,
            'data' => $data,
            'models' => $orders,
            'is_specific' => false,
            'active_count' => $orders->count(),
        ];
    }

    /**
     * Format collection of orders to array data.
     */
    protected function formatOrdersData($orders): array
    {
        return $orders->map(function (Order $o) {
            return [
                'order_number' => $o->order_number,
                'status' => $o->status,
                'total_amount' => 'Rs. '.number_format((float) $o->total_amount, 2),
                'payment_method' => strtoupper($o->payment_method),
                'date' => $o->created_at->format('M d, Y h:i A'),
                'tracking_updates' => [
                    'confirmed_at' => $o->confirmed_at?->format('M d, Y h:i A'),
                    'shipped_at' => $o->shipped_at?->format('M d, Y h:i A'),
                    'out_for_delivery_at' => $o->out_for_delivery_at?->format('M d, Y h:i A'),
                    'delivered_at' => $o->delivered_at?->format('M d, Y h:i A'),
                ],
                'items' => $o->items->map(fn ($item) => "{$item->product_name} (x{$item->quantity})")->toArray(),
            ];
        })->toArray();
    }

    /**
     * Fallback rule-based formatter when API key is missing or offline.
     */
    protected function buildFallbackReply(string $query, array $productData, array $orderContext, ?int $userId): string
    {
        $queryLower = Str::lower($query);

        // Order status query
        if (Str::contains($queryLower, ['order', 'delivery', 'status', 'track', 'shipped', 'where is'])) {
            if (! $userId || ($orderContext['auth_required'] ?? false)) {
                return 'Please sign in to your FlavourFlow account to check your order details and delivery status.';
            }

            $orderData = $orderContext['data'] ?? [];

            if ($orderContext['is_specific'] ?? false) {
                if (empty($orderData)) {
                    return "We couldn't find the requested order associated with your account.";
                }

                $order = $orderData[0];
                $itemsList = implode(', ', $order['items'] ?? []);

                return "Here is the status of Order #{$order['order_number']}:\n"
                    ."• Status: {$order['status']}\n"
                    ."• Total: {$order['total_amount']}\n"
                    ."• Items: {$itemsList}\n"
                    ."• Date: {$order['date']}";
            }

            $activeCount = $orderContext['active_count'] ?? 0;

            if ($activeCount === 0) {
                return "You don't have any active orders to track.";
            }

            if ($activeCount === 1) {
                $order = $orderData[0];
                $itemsList = implode(', ', $order['items'] ?? []);

                return "Here is the status of your active Order #{$order['order_number']}:\n"
                    ."• Status: {$order['status']}\n"
                    ."• Total: {$order['total_amount']}\n"
                    ."• Items: {$itemsList}\n"
                    ."• Date: {$order['date']}";
            }

            // Multiple active orders: Ask user to select
            $reply = "Which order would you like to check?\n";
            foreach ($orderData as $index => $order) {
                $num = $index + 1;
                $reply .= "{$num}. #{$order['order_number']} — {$order['status']}\n";
            }
            $reply .= 'Please select an order.';

            return trim($reply);
        }

        // Budget recommendation or product query
        if (! empty($productData)) {
            $response = "Here are matching products from our spice collection:\n\n";
            foreach ($productData as $p) {
                $response .= "• {$p['name']} - {$p['price']} ({$p['stock_status']})\n  {$p['description']}\n\n";
            }

            return trim($response);
        }

        return 'Welcome to FlavourFlow! How can I assist you today with our fresh spices, blends, or orders?';
    }
}
