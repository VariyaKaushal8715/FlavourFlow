<?php

namespace App\AI\Contracts;

interface AiResponseInterface
{
    /**
     * Determine if the AI response execution was successful.
     */
    public function isSuccess(): bool;

    /**
     * Get response payload data.
     *
     * @return array<string, mixed>
     */
    public function getData(): array;

    /**
     * Get response status message or details.
     */
    public function getMessage(): string;
}
