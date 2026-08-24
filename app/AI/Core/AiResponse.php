<?php

namespace App\AI\Core;

use App\AI\Contracts\AiResponseInterface;

class AiResponse implements AiResponseInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        protected bool $success,
        protected array $data = [],
        protected string $message = ''
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
