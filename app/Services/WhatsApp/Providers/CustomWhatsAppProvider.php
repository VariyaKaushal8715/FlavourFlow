<?php

namespace App\Services\WhatsApp\Providers;

use App\Services\WhatsApp\WhatsAppProviderInterface;
use App\Services\WhatsApp\WhatsAppSendResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CustomWhatsAppProvider implements WhatsAppProviderInterface
{
    protected ?string $apiUrl;
    protected ?string $apiKey;
    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->apiUrl = $config['api_url'] ?? config('order_notifications.whatsapp.custom.api_url');
        $this->apiKey = $config['api_key'] ?? config('order_notifications.whatsapp.custom.api_key');
        $this->timeout = (int) ($config['timeout'] ?? config('order_notifications.whatsapp.timeout', 10));
    }

    public function send(string $to, string $message, array $metadata = []): WhatsAppSendResult
    {
        if (empty($this->apiUrl)) {
            $error = 'Custom WhatsApp API URL not configured.';
            Log::warning($error);
            return WhatsAppSendResult::failure($error);
        }

        try {
            $request = Http::timeout($this->timeout);
            if (! empty($this->apiKey)) {
                $request = $request->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey]);
            }

            $response = $request->post($this->apiUrl, [
                'to' => $to,
                'message' => $message,
                'metadata' => $metadata,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return WhatsAppSendResult::success($data['id'] ?? $data['message_id'] ?? null, is_array($data) ? $data : []);
            }

            $errorMsg = 'Custom WhatsApp API error: HTTP ' . $response->status() . ' - ' . $response->body();
            Log::error($errorMsg);
            return WhatsAppSendResult::failure($errorMsg);
        } catch (Throwable $e) {
            Log::error('Custom WhatsApp dispatch exception: ' . $e->getMessage(), ['exception' => $e]);
            return WhatsAppSendResult::failure('Custom WhatsApp exception: ' . $e->getMessage());
        }
    }
}
