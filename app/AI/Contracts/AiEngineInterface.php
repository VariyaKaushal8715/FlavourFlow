<?php

namespace App\AI\Contracts;

interface AiEngineInterface
{
    /**
     * Process an AI request through the core engine using the bound adapter.
     */
    public function process(AiRequestInterface $request): AiResponseInterface;

    /**
     * Get the active domain adapter instance.
     */
    public function getAdapter(): AiAdapterInterface;

    /**
     * Check if the core engine and active adapter are ready for operation.
     */
    public function isReady(): bool;
}
