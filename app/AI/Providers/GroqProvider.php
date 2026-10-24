<?php

namespace App\AI\Providers;

use App\AI\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class GroqProvider implements AiProviderInterface
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    private int $timeout;

    /**
     * @param  array{api_key?: string, model?: string, base_url?: string, timeout?: int}  $config
     */
    public function __construct(array $config = [])
    {
        $this->apiKey = (string) ($config['api_key'] ?? config('ai.providers.groq.api_key', ''));
        $this->model = (string) ($config['model'] ?? config('ai.providers.groq.model', 'llama-3.3-70b-versatile'));
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? config('ai.providers.groq.base_url', 'https://api.groq.com/openai/v1')), '/');
        $this->timeout = (int) ($config['timeout'] ?? config('ai.providers.groq.timeout', 15));
    }

    public function getName(): string
    {
        return 'groq';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function isAvailable(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Performs a lightweight, zero-token connectivity check against Groq models endpoint.
     *
     * @return array{success: bool, message: string, details: array<string, mixed>}
     */
    public function testConnection(): array
    {
        if (! $this->isAvailable()) {
            return [
                'success' => false,
                'message' => 'Groq API Key is not configured in .env (GROQ_API_KEY).',
                'details' => [
                    'provider' => 'groq',
                    'configured' => false,
                ],
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'User-Agent' => config('app.name', 'FlavourFlow').'/1.0',
            ])
                ->timeout(8)
                ->get("{$this->baseUrl}/models");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "Successfully connected to Groq API. Active model: {$this->model}.",
                    'details' => [
                        'provider' => 'groq',
                        'status' => $response->status(),
                        'model' => $this->model,
                    ],
                ];
            }

            $errorMsg = $response->json('error.message') ?? "HTTP {$response->status()}";

            return [
                'success' => false,
                'message' => "Groq API returned HTTP status {$response->status()}: {$errorMsg}",
                'details' => [
                    'provider' => 'groq',
                    'status' => $response->status(),
                    'error' => $errorMsg,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Connection to Groq failed: '.$e->getMessage(),
                'details' => [
                    'provider' => 'groq',
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Generate response via Groq OpenAI-compatible chat completions endpoint.
     *
     * @param  array{system: string, user: string, context?: array<string, mixed>, language?: string}  $prompt
     * @return array{content: string, tokens_used: int, model: string, raw: array<string, mixed>, success: bool, error: ?string}
     */
    public function generate(array $prompt): array
    {
        if (! $this->isAvailable()) {
            return [
                'content' => '',
                'tokens_used' => 0,
                'model' => $this->model,
                'raw' => [],
                'success' => false,
                'error' => 'Groq API Key not configured.',
            ];
        }

        try {
            $systemContent = $prompt['system'] ?? 'You are the FlavourFlow AI shopping assistant.';
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

                logger()->info('Groq AI Generation Successful', [
                    'model' => $this->model,
                    'tokens_used' => $tokensUsed,
                ]);

                return [
                    'content' => trim((string) $content),
                    'tokens_used' => $tokensUsed,
                    'model' => $this->model,
                    'raw' => $json,
                    'success' => true,
                    'error' => null,
                ];
            }

            $errorMsg = $response->json('error.message') ?? "HTTP {$response->status()}";
            logger()->error("Groq API Error [HTTP {$response->status()}]: {$errorMsg}");

            return [
                'content' => '',
                'tokens_used' => 0,
                'model' => $this->model,
                'raw' => $response->json() ?? [],
                'success' => false,
                'error' => "Groq error: {$errorMsg}",
            ];
        } catch (Throwable $e) {
            logger()->error('Groq Exception: '.$e->getMessage());

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
