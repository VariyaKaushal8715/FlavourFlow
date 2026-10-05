<?php

namespace App\Services\WhatsApp\Providers;

use App\Services\WhatsApp\WhatsAppProviderInterface;
use App\Services\WhatsApp\WhatsAppSendResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogWhatsAppProvider implements WhatsAppProviderInterface
{
    public function send(string $to, string $message, array $metadata = []): WhatsAppSendResult
    {
        $messageId = 'LOG-WA-'.Str::uuid()->toString();

        Log::info('WhatsApp Notification Dispatched', [
            'to' => $to,
            'message' => $message,
            'message_id' => $messageId,
            'metadata' => $metadata,
        ]);

        return WhatsAppSendResult::success($messageId, [
            'logged' => true,
            'to' => $to,
        ]);
    }
}
