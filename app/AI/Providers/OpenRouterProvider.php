<?php

namespace App\AI\Providers;

use App\AI\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class OpenRouterProvider implements AiProviderInterface
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    private int $timeout;

    public function __construct(array $config = [])
    {
        $this->apiKey = (string) ($config['api_key'] ?? config('ai.providers.openrouter.api_key', ''));
        $this->model = (string) ($config['model'] ?? config('ai.providers.openrouter.model', 'google/gemini-2.0-flash-001'));
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? config('ai.providers.openrouter.base_url', 'https://openrouter.ai/api/v1')), '/');
        $this->timeout = (int) ($config['timeout'] ?? config('ai.providers.openrouter.timeout', 15));
    }

    public function getName(): string
    {
        return 'openrouter';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function isAvailable(): bool
    {
        return $this->apiKey !== '';
    }

    public function testConnection(): array
    {
        if (! $this->isAvailable()) {
            return [
                'success' => false,
                'message' => 'OpenRouter API Key is not configured in .env (OPENROUTER_API_KEY).',
                'details' => [
                    'provider' => 'openrouter',
                    'configured' => false,
                ],
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'HTTP-Referer' => config('app.url', 'http://localhost'),
                'X-Title' => config('app.name', 'FlavourFlow'),
            ])
                ->timeout(8)
                ->get("{$this->baseUrl}/models");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "Successfully connected to OpenRouter API. Active model: {$this->model}.",
                    'details' => [
                        'provider' => 'openrouter',
                        'status' => $response->status(),
                        'model' => $this->model,
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => "OpenRouter API returned HTTP status {$response->status()}.",
                'details' => [
                    'provider' => 'openrouter',
                    'status' => $response->status(),
                    'error' => $response->json('error.message') ?? $response->body(),
                ],
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Connection to OpenRouter failed: '.$e->getMessage(),
                'details' => [
                    'provider' => 'openrouter',
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }

    public function generate(array $prompt): array
    {
        if (! $this->isAvailable()) {
            return [
                'content' => '',
                'tokens_used' => 0,
                'model' => $this->model,
                'raw' => [],
                'success' => false,
                'error' => 'OpenRouter API Key not configured.',
            ];
        }

        try {
            $systemContent = $prompt['system'] ?? 'You are the FlavourFlow AI assistant.';
            $userContent = $prompt['user'] ?? '';
            $context = $prompt['context'] ?? [];

            if (! empty($context)) {
                $userContent .= "\n\nStructured Context Payload:\n".json_encode($context);
            }

            $messages = [
                ['role' => 'system', 'content' => $systemContent],
                ['role' => 'user', 'content' => $userContent],
            ];

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'HTTP-Referer' => config('app.url', 'http://localhost'),
                'X-Title' => config('app.name', 'FlavourFlow'),
                'Content-Type' => 'application/json',
            ])
                ->timeout($this->timeout)
                ->retry(2, 500)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => 0.4,
                    'max_tokens' => 800,
                ]);

            if ($response->successful()) {
                $json = $response->json();
                $content = $json['choices'][0]['message']['content'] ?? '';
                $tokensUsed = (int) ($json['usage']['total_tokens'] ?? 0);

                // Safe logging without leaking prompt contents or keys
                logger()->info('OpenRouter AI Generation Successful', [
                    'model' => $this->model,
                    'tokens_used' => $tokensUsed,
                ]);

                return [
                    'content' => trim($content),
                    'tokens_used' => $tokensUsed,
                    'model' => $this->model,
                    'raw' => $json,
                    'success' => true,
                    'error' => null,
                ];
            }

            $errorMsg = $response->json('error.message') ?? "HTTP {$response->status()}";
            logger()->error("OpenRouter API Error [HTTP {$response->status()}]: {$errorMsg}");

            return [
                'content' => '',
                'tokens_used' => 0,
                'model' => $this->model,
                'raw' => $response->json() ?? [],
                'success' => false,
                'error' => "OpenRouter error: {$errorMsg}",
            ];
        } catch (Throwable $e) {
            logger()->error('OpenRouter Exception: '.$e->getMessage());

            return [
                'content' => '',
                'tokens_used' => 0,
                'model' => $this->model,
                'raw' => [],
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getSupportedLanguages(): array
    {
        return ['en', 'hi', 'gu', 'hinglish', 'gujenglish'];
    }
}
