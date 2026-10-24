<?php

namespace App\AI\Providers;

use App\AI\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class GeminiProvider implements AiProviderInterface
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
        $this->apiKey = (string) ($config['api_key'] ?? config('ai.providers.gemini.api_key', ''));
        $this->model = (string) ($config['model'] ?? config('ai.providers.gemini.model', 'gemini-2.0-flash'));
        $this->baseUrl = rtrim((string) ($config['base_url'] ?? config('ai.providers.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta')), '/');
        $this->timeout = (int) ($config['timeout'] ?? config('ai.providers.gemini.timeout', 15));
    }

    public function getName(): string
    {
        return 'gemini';
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
     * Performs a lightweight, zero-cost connectivity check against Gemini models list endpoint.
     *
     * @return array{success: bool, message: string, details: array<string, mixed>}
     */
    public function testConnection(): array
    {
        if (! $this->isAvailable()) {
            return [
                'success' => false,
                'message' => 'Gemini API Key is not configured in .env (GEMINI_API_KEY).',
                'details' => [
                    'provider' => 'gemini',
                    'configured' => false,
                ],
            ];
        }

        try {
            $response = Http::timeout(8)
                ->get("{$this->baseUrl}/models", [
                    'key' => $this->apiKey,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "Successfully connected to Google Gemini API. Active model: {$this->model}.",
                    'details' => [
                        'provider' => 'gemini',
                        'status' => $response->status(),
                        'model' => $this->model,
                    ],
                ];
            }

            $errorMsg = $response->json('error.message') ?? "HTTP {$response->status()}";

            return [
                'success' => false,
                'message' => "Gemini API returned HTTP status {$response->status()}: {$errorMsg}",
                'details' => [
                    'provider' => 'gemini',
                    'status' => $response->status(),
                    'error' => $errorMsg,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Connection to Gemini failed: '.$e->getMessage(),
                'details' => [
                    'provider' => 'gemini',
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Generate reasoning response via Gemini generateContent endpoint.
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
                'error' => 'Gemini API Key not configured.',
            ];
        }

        try {
            $systemContent = $prompt['system'] ?? 'You are the FlavourFlow AI shopping assistant.';
            $userContent = $prompt['user'] ?? '';
            $context = $prompt['context'] ?? [];

            if (! empty($context)) {
                $userContent .= "\n\nStructured Context Payload:\n".json_encode($context);
            }

            $endpoint = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->timeout($this->timeout)
                ->retry(2, 500)
                ->post($endpoint, [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemContent],
                        ],
                    ],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $userContent],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 800,
                    ],
                ]);

            if ($response->successful()) {
                $json = $response->json();
                $content = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $tokensUsed = (int) ($json['usageMetadata']['totalTokenCount'] ?? 0);

                logger()->info('Gemini AI Generation Successful', [
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
            logger()->error("Gemini API Error [HTTP {$response->status()}]: {$errorMsg}");

            return [
                'content' => '',
                'tokens_used' => 0,
                'model' => $this->model,
                'raw' => $response->json() ?? [],
                'success' => false,
                'error' => "Gemini error: {$errorMsg}",
            ];
        } catch (Throwable $e) {
            logger()->error('Gemini Exception: '.$e->getMessage());

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
