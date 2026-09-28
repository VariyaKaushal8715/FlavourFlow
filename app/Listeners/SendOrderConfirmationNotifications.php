<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Services\OrderNotificationService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOrderConfirmationNotifications
{
    public function __construct(
        protected OrderNotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        try {
            $this->notificationService->sendOrderConfirmation($event->order);
        } catch (Throwable $e) {
            Log::error("Failed executing SendOrderConfirmationNotifications listener for order #{$event->order->order_number}: ".$e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }
}
