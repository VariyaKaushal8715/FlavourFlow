<?php

namespace App\AI\Contracts;

interface AiAdapterInterface
{
    /**
     * Get the unique adapter identifier name (e.g. 'flavourflow').
     */
    public function getName(): string;

    /**
     * Map a domain payload request to standard AI payload format.
     *
     * @return array<string, mixed>
     */
    public function mapRequest(AiRequestInterface $request): array;

    /**
     * Map raw AI engine response back to structured AiResponseInterface.
     *
     * @param  array<string, mixed>  $rawResponse
     */
    public function mapResponse(array $rawResponse): AiResponseInterface;

    /**
     * Verify domain connection and adapter availability.
     */
    public function isConnected(): bool;
}
