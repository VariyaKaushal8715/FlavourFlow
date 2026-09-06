<?php

namespace App\Services\WhatsApp;

use App\Services\WhatsApp\Providers\CustomWhatsAppProvider;
use App\Services\WhatsApp\Providers\LogWhatsAppProvider;
use App\Services\WhatsApp\Providers\MetaWhatsAppProvider;
use App\Services\WhatsApp\Providers\TwilioWhatsAppProvider;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class WhatsAppService
{
    protected ?WhatsAppProviderInterface $provider = null;

    public function __construct(?WhatsAppProviderInterface $provider = null)
    {
        $this->provider = $provider;
    }

    /**
     * Resolve or get the configured provider.
     */
    public function getProvider(): WhatsAppProviderInterface
    {
        if ($this->provider !== null) {
            return $this->provider;
        }

        $driver = config('order_notifications.whatsapp.provider', 'log');

        return match (strtolower($driver)) {
            'twilio' => new TwilioWhatsAppProvider(),
            'meta' => new MetaWhatsAppProvider(),
            'custom' => new CustomWhatsAppProvider(),
            'log', 'mock', 'null' => new LogWhatsAppProvider(),
            default => new LogWhatsAppProvider(),
        };
    }

    /**
     * Send a WhatsApp message safely with full error handling.
     * Never throws exceptions so checkout is never broken.
     */
    public function sendMessage(string $to, string $message, array $metadata = []): WhatsAppSendResult
    {
        if (! config('order_notifications.whatsapp.enabled', true)) {
            Log::info("WhatsApp notifications are globally disabled. Skipping dispatch to {$to}.");
            return WhatsAppSendResult::failure('WhatsApp notifications disabled in configuration.');
        }

        if (empty(trim($to))) {
            return WhatsAppSendResult::failure('Recipient phone number is empty.');
        }

        try {
            return $this->getProvider()->send($to, $message, $metadata);
        } catch (Throwable $e) {
            Log::error('WhatsAppService unexpected failure: ' . $e->getMessage(), ['exception' => $e]);
            return WhatsAppSendResult::failure('Unexpected error sending WhatsApp message: ' . $e->getMessage());
        }
    }
}
