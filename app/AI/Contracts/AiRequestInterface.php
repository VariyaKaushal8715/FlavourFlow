<?php

namespace App\AI\Contracts;

interface AiRequestInterface
{
    /**
     * Get the AI action name (e.g. 'recommend_products', 'analyze_sales', 'chat').
     */
    public function getAction(): string;

    /**
     * Get the primary payload array.
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array;

    /**
     * Get additional contextual metadata.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array;
}
