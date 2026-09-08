<?php

namespace App\Services\WhatsApp\Providers;

use App\Services\WhatsApp\WhatsAppProviderInterface;
use App\Services\WhatsApp\WhatsAppSendResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TwilioWhatsAppProvider implements WhatsAppProviderInterface
{
    protected ?string $sid;
    protected ?string $token;
    protected ?string $from;
    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->sid = $config['sid'] ?? config('order_notifications.whatsapp.twilio.sid');
        $this->token = $config['token'] ?? config('order_notifications.whatsapp.twilio.token');
        $this->from = $config['from'] ?? config('order_notifications.whatsapp.twilio.from');
        $this->timeout = (int) ($config['timeout'] ?? config('order_notifications.whatsapp.timeout', 10));
    }

    public function send(string $to, string $message, array $metadata = []): WhatsAppSendResult
    {
        if (empty($this->sid) || empty($this->token) || empty($this->from)) {
            $error = 'Twilio WhatsApp credentials not fully configured.';
            Log::warning($error);
            return WhatsAppSendResult::failure($error);
        }

        // Format recipient to WhatsApp international E.164
        $cleanTo = preg_replace('/[^\d+]/', '', $to);
        if (! str_starts_with($cleanTo, '+')) {
            $cleanTo = '+' . $cleanTo;
        }
        $formattedTo = 'whatsapp:' . $cleanTo;
        $formattedFrom = str_starts_with($this->from, 'whatsapp:') ? $this->from : 'whatsapp:' . $this->from;

        try {
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->sid}/Messages.json";

            $response = Http::withBasicAuth($this->sid, $this->token)
                ->timeout($this->timeout)
                ->asForm()
                ->post($url, [
                    'From' => $formattedFrom,
                    'To' => $formattedTo,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return WhatsAppSendResult::success($data['sid'] ?? null, $data);
            }

            $errorMsg = 'Twilio error: ' . ($response->json()['message'] ?? $response->body());
            Log::error($errorMsg);
            return WhatsAppSendResult::failure($errorMsg, $response->json() ?? []);
        } catch (Throwable $e) {
            Log::error('Twilio WhatsApp dispatch exception: ' . $e->getMessage(), ['exception' => $e]);
            return WhatsAppSendResult::failure('Twilio connection exception: ' . $e->getMessage());
        }
    }
}
