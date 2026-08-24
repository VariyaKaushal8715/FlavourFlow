<?php

namespace App\AI\Adapters;

use App\AI\Contracts\AiAdapterInterface;
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
}
