<?php

namespace App\Services\WhatsApp\Providers;

use App\Services\WhatsApp\WhatsAppProviderInterface;
use App\Services\WhatsApp\WhatsAppSendResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MetaWhatsAppProvider implements WhatsAppProviderInterface
{
    protected ?string $apiUrl;

    protected ?string $phoneNumberId;

    protected ?string $accessToken;

    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->apiUrl = $config['api_url'] ?? $config['apiUrl'] ?? config('order_notifications.whatsapp.meta.api_url', 'https://graph.facebook.com/v19.0');
        $this->phoneNumberId = $config['phone_number_id'] ?? $config['phoneNumberId'] ?? config('order_notifications.whatsapp.meta.phone_number_id');
        $this->accessToken = $config['access_token'] ?? $config['accessToken'] ?? config('order_notifications.whatsapp.meta.access_token');
        $this->timeout = (int) ($config['timeout'] ?? config('order_notifications.whatsapp.timeout', 10));
    }

    public function send(string $to, string $message, array $metadata = []): WhatsAppSendResult
    {
        if (empty($this->phoneNumberId) || empty($this->accessToken)) {
            $error = 'Meta WhatsApp Cloud API credentials not fully configured.';
            Log::warning($error);

            return WhatsAppSendResult::failure($error);
        }

        // Clean phone number (digits only, e.g. 919876543210)
        $cleanTo = preg_replace('/[^\d]/', '', $to);

        $url = rtrim($this->apiUrl, '/')."/{$this->phoneNumberId}/messages";

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout($this->timeout)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $cleanTo,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => true,
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['messages'][0]['id'] ?? null;

                return WhatsAppSendResult::success($messageId, $data);
            }

            $errorMsg = 'Meta WhatsApp error: '.($response->json()['error']['message'] ?? $response->body());
            Log::error($errorMsg);

            return WhatsAppSendResult::failure($errorMsg, $response->json() ?? []);
        } catch (Throwable $e) {
            Log::error('Meta WhatsApp dispatch exception: '.$e->getMessage(), ['exception' => $e]);

            return WhatsAppSendResult::failure('Meta WhatsApp exception: '.$e->getMessage());
        }
    }
}
