<?php

namespace App\AI\Core;

use App\AI\Contracts\AiRequestInterface;

class AiRequest implements AiRequestInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        protected string $action,
        protected array $payload = [],
        protected array $context = []
    ) {}

    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
