<?php

namespace App\Services;

use App\AI\Services\CustomerAiAssistantService;
use App\Models\Product;
use Illuminate\Support\Collection;

class ChatbotService
{
    public function __construct(protected ?CustomerAiAssistantService $assistant = null)
    {
        $this->assistant = $assistant ?? app(CustomerAiAssistantService::class);
    }

    /**
     * Process user chat message securely through the unified AI assistant service.
     *
     * @return array{reply: string, products: Collection<int, Product>, orders: list<array<string, mixed>>}
     */
    public function reply(string $userMessage, ?int $userId = null): array
    {
        $cleanQuery = trim($userMessage);
        if ($cleanQuery === '') {
            return [
                'reply' => 'Please type a question or product request.',
                'products' => collect(),
                'orders' => [],
            ];
        }

        $result = $this->assistant->processMessage($cleanQuery, $userId, session()->getId());

        // Map product models if needed by callers
        $productModels = collect($result['products'] ?? [])->map(function (array $p): ?Product {
            return isset($p['id']) ? Product::find($p['id']) : null;
        })->filter()->values();

        return [
            'reply' => $result['message'],
            'products' => $productModels,
            'orders' => $result['actions'] ?? [],
        ];
    }
}
