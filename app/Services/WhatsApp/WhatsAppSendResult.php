<?php

namespace App\Services\WhatsApp;

class WhatsAppSendResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $messageId = null,
        public readonly ?string $error = null,
        public readonly array $rawResponse = []
    ) {}

    public static function success(?string $messageId = null, array $rawResponse = []): self
    {
        return new self(
            success: true,
            messageId: $messageId,
            rawResponse: $rawResponse
        );
    }

    public static function failure(string $error, array $rawResponse = []): self
    {
        return new self(
            success: false,
            error: $error,
            rawResponse: $rawResponse
        );
    }
}
