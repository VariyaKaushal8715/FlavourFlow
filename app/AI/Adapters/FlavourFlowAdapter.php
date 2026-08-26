<?php

namespace App\AI\Adapters;

use App\AI\Contracts\AiAdapterInterface;
use App\AI\Contracts\AiAnalyzerInterface;
use App\AI\Contracts\AiContextBuilderInterface;
use App\AI\Contracts\AiRequestInterface;
use App\AI\Contracts\AiResponseInterface;
use App\AI\Core\AiResponse;

class FlavourFlowAdapter implements AiAdapterInterface
{
    public function getName(): string
    {
        return 'flavourflow';
    }

    public function mapRequest(AiRequestInterface $request): array
    {
        return [
            'domain' => 'FlavourFlow Spices & Pantry',
            'action' => $request->getAction(),
            'payload' => $request->getPayload(),
            'context' => array_merge([
                'app_name' => config('app.name', 'FlavourFlow'),
                'locale' => app()->getLocale(),
            ], $request->getContext()),
        ];
    }

    public function mapResponse(array $rawResponse): AiResponseInterface
    {
        $success = ($rawResponse['status'] ?? '') === 'processed';

        return new AiResponse(
            success: $success,
            data: $rawResponse,
            message: $success ? 'FlavourFlow AI Core executed successfully.' : 'FlavourFlow AI Core execution failed.'
        );
    }

    public function isConnected(): bool
    {
        return true;
    }

    /**
     * Build domain context for a user or session.
     *
     * @return array<string, mixed>
     */
    public function buildDomainContext(?int $userId = null, ?string $sessionId = null): array
    {
        /** @var AiContextBuilderInterface $builder */
        $builder = app(AiContextBuilderInterface::class);

        return $builder->buildContext($userId, $sessionId);
    }

    /**
     * Analyze user context to produce domain insights.
     *
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function analyzeDomainContext(array $context): array
    {
        /** @var AiAnalyzerInterface $analyzer */
        $analyzer = app(AiAnalyzerInterface::class);

        return $analyzer->analyze($context);
    }
}
