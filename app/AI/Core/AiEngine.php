<?php

namespace App\AI\Core;

use App\AI\Contracts\AiAdapterInterface;
use App\AI\Contracts\AiEngineInterface;
use App\AI\Contracts\AiRequestInterface;
use App\AI\Contracts\AiResponseInterface;

class AiEngine implements AiEngineInterface
{
    public function __construct(
        protected AiAdapterInterface $adapter
    ) {}

    public function process(AiRequestInterface $request): AiResponseInterface
    {
        $mappedRequest = $this->adapter->mapRequest($request);

        // Core processing logic (provider-independent simulation payload)
        $rawOutput = [
            'status' => 'processed',
            'action' => $request->getAction(),
            'payload' => $mappedRequest,
            'processed_at' => now()->toIso8601String(),
        ];

        return $this->adapter->mapResponse($rawOutput);
    }

    public function getAdapter(): AiAdapterInterface
    {
        return $this->adapter;
    }

    public function isReady(): bool
    {
        return (bool) config('ai.enabled', false) && $this->adapter->isConnected();
    }
}
