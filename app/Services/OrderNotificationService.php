<?php

namespace App\Services;

use App\Mail\AdminNewOrderAlertMail;
use App\Mail\CustomerOrderConfirmationMail;
use App\Models\Order;
use App\Models\OrderDeliveryNotification;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class OrderNotificationService
{
    public function __construct(
        protected ?WhatsAppService $whatsAppService = null
    ) {
        $this->whatsAppService = $whatsAppService ?? app(WhatsAppService::class);
    }

    /**
     * Send all confirmation notifications for a successfully placed order.
     *
     * @param  Order  $order
     * @return array Summary of notification statuses
     */
    public function sendOrderConfirmation(Order $order): array
    {
        // Ensure relationships are loaded
        $order->loadMissing(['items.product', 'user']);

        $results = [
            'customer_whatsapp' => false,
            'customer_email' => false,
            'admin_whatsapp' => false,
            'admin_email' => false,
        ];

        // Central store & seller contact details
        $storeInfo = config('order_notifications.store', [
            'name' => 'FlavourFlow',
            'phone' => '+91 99999 99999',
            'email' => 'support@flavourflow.com',
            'whatsapp' => '+91 99999 99999',
            'address' => 'Patan, Gujarat, India',
        ]);

        // Secure tracking link (cryptographically signed temporary link valid for 30 days)
        $trackingUrl = $this->generateSecureTrackingUrl($order);

        // Secure admin order URL
        $adminOrderUrl = route('admin.orders.show', $order);

        // 1. Customer WhatsApp Notification
        $results['customer_whatsapp'] = $this->sendCustomerWhatsApp($order, $trackingUrl, $storeInfo);

        // 2. Customer Email Notification
        $results['customer_email'] = $this->sendCustomerEmail($order, $trackingUrl, $storeInfo);

        // 3. Admin / Seller WhatsApp Notification
        $results['admin_whatsapp'] = $this->sendAdminWhatsApp($order, $adminOrderUrl, $storeInfo);

        // 4. Admin / Seller Email Notification
        $results['admin_email'] = $this->sendAdminEmail($order, $adminOrderUrl, $storeInfo);

        return $results;
    }

    /**
     * Generate secure cryptographic tracking URL for the customer.
     */
    public function generateSecureTrackingUrl(Order $order): string
    {
        try {
            return URL::temporarySignedRoute(
                'orders.track.signed',
                now()->addDays(30),
                ['order' => $order->order_number]
            );
        } catch (Throwable $e) {
            Log::warning('Failed generating signed route, falling back to authenticated route: ' . $e->getMessage());
            return route('account.orders.track', $order->order_number);
        }
    }

    /**
     * Send Customer WhatsApp notification with idempotency check.
     */
    protected function sendCustomerWhatsApp(Order $order, string $trackingUrl, array $storeInfo): bool
    {
        if (! config('order_notifications.channels.customer_whatsapp', true) || ! config('order_notifications.whatsapp.enabled', true)) {
            return false;
        }

        $recipient = $order->mobile ?? $order->user?->profile?->mobile_number;
        if (empty($recipient)) {
            Log::info("Skipping Customer WhatsApp for Order #{$order->order_number}: No mobile number available.");
            $this->recordNotification($order, OrderDeliveryNotification::CHANNEL_CUSTOMER_WHATSAPP, 'none', OrderDeliveryNotification::STATUS_SKIPPED, 'No mobile number provided.');
            return false;
        }

        // Idempotency check: Don't resend if already sent
        if ($this->hasAlreadySent($order, OrderDeliveryNotification::CHANNEL_CUSTOMER_WHATSAPP)) {
            Log::info("Customer WhatsApp for Order #{$order->order_number} already sent. Skipping duplicate.");
            return true;
        }

        $message = $this->formatCustomerWhatsAppMessage($order, $trackingUrl, $storeInfo);

        try {
            $sendResult = $this->whatsAppService->sendMessage($recipient, $message, [
                'type' => 'order_confirmation',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            if ($sendResult->success) {
                $this->recordNotification(
                    $order,
                    OrderDeliveryNotification::CHANNEL_CUSTOMER_WHATSAPP,
                    $recipient,
                    OrderDeliveryNotification::STATUS_SENT,
                    null,
                    ['message_id' => $sendResult->messageId]
                );
                return true;
            } else {
                $this->recordNotification(
                    $order,
                    OrderDeliveryNotification::CHANNEL_CUSTOMER_WHATSAPP,
                    $recipient,
                    OrderDeliveryNotification::STATUS_FAILED,
                    $sendResult->error
                );
                return false;
            }
        } catch (Throwable $e) {
            Log::error("Customer WhatsApp error for Order #{$order->order_number}: " . $e->getMessage(), ['exception' => $e]);
            $this->recordNotification(
                $order,
                OrderDeliveryNotification::CHANNEL_CUSTOMER_WHATSAPP,
                $recipient,
                OrderDeliveryNotification::STATUS_FAILED,
                $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Send Customer Email notification with idempotency check.
     */
    protected function sendCustomerEmail(Order $order, string $trackingUrl, array $storeInfo): bool
    {
        if (! config('order_notifications.channels.customer_email', true) || ! config('order_notifications.email.enabled', true)) {
            return false;
        }

        $recipient = $order->email ?? $order->user?->email;
        if (empty($recipient)) {
            Log::info("Skipping Customer Email for Order #{$order->order_number}: No email address available.");
            $this->recordNotification($order, OrderDeliveryNotification::CHANNEL_CUSTOMER_EMAIL, 'none', OrderDeliveryNotification::STATUS_SKIPPED, 'No email address provided.');
            return false;
        }

        // Idempotency check: Don't resend if already sent
        if ($this->hasAlreadySent($order, OrderDeliveryNotification::CHANNEL_CUSTOMER_EMAIL)) {
            Log::info("Customer Email for Order #{$order->order_number} already sent. Skipping duplicate.");
            return true;
        }

        try {
            Mail::to($recipient)->send(new CustomerOrderConfirmationMail($order, $trackingUrl, $storeInfo));

            $this->recordNotification(
                $order,
                OrderDeliveryNotification::CHANNEL_CUSTOMER_EMAIL,
                $recipient,
                OrderDeliveryNotification::STATUS_SENT
            );
            return true;
        } catch (Throwable $e) {
            Log::error("Customer Email dispatch failed for Order #{$order->order_number}: " . $e->getMessage(), ['exception' => $e]);
            $this->recordNotification(
                $order,
                OrderDeliveryNotification::CHANNEL_CUSTOMER_EMAIL,
                $recipient,
                OrderDeliveryNotification::STATUS_FAILED,
                $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Send Admin WhatsApp notification with idempotency check.
     */
    protected function sendAdminWhatsApp(Order $order, string $adminOrderUrl, array $storeInfo): bool
    {
        if (! config('order_notifications.channels.admin_whatsapp', true) || ! config('order_notifications.whatsapp.enabled', true)) {
            return false;
        }

        $adminPhone = config('order_notifications.admin.whatsapp', config('personal_site.contact.phone'));
        if (empty($adminPhone)) {
            Log::info("Skipping Admin WhatsApp for Order #{$order->order_number}: No admin WhatsApp configured.");
            $this->recordNotification($order, OrderDeliveryNotification::CHANNEL_ADMIN_WHATSAPP, 'none', OrderDeliveryNotification::STATUS_SKIPPED, 'No admin WhatsApp configured.');
            return false;
        }

        // Idempotency check: Don't resend if already sent
        if ($this->hasAlreadySent($order, OrderDeliveryNotification::CHANNEL_ADMIN_WHATSAPP)) {
            Log::info("Admin WhatsApp for Order #{$order->order_number} already sent. Skipping duplicate.");
            return true;
        }

        $message = $this->formatAdminWhatsAppMessage($order, $adminOrderUrl, $storeInfo);

        try {
            $sendResult = $this->whatsAppService->sendMessage($adminPhone, $message, [
                'type' => 'admin_new_order_alert',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            if ($sendResult->success) {
                $this->recordNotification(
                    $order,
                    OrderDeliveryNotification::CHANNEL_ADMIN_WHATSAPP,
                    $adminPhone,
                    OrderDeliveryNotification::STATUS_SENT,
                    null,
                    ['message_id' => $sendResult->messageId]
                );
                return true;
            } else {
                $this->recordNotification(
                    $order,
                    OrderDeliveryNotification::CHANNEL_ADMIN_WHATSAPP,
                    $adminPhone,
                    OrderDeliveryNotification::STATUS_FAILED,
                    $sendResult->error
                );
                return false;
            }
        } catch (Throwable $e) {
            Log::error("Admin WhatsApp error for Order #{$order->order_number}: " . $e->getMessage(), ['exception' => $e]);
            $this->recordNotification(
                $order,
                OrderDeliveryNotification::CHANNEL_ADMIN_WHATSAPP,
                $adminPhone,
                OrderDeliveryNotification::STATUS_FAILED,
                $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Send Admin Email notification with idempotency check.
     */
    protected function sendAdminEmail(Order $order, string $adminOrderUrl, array $storeInfo): bool
    {
        if (! config('order_notifications.channels.admin_email', true) || ! config('order_notifications.email.enabled', true)) {
            return false;
        }

        $adminEmail = config('order_notifications.admin.email', env('ADMIN_EMAIL', 'admin@flavourflow.test'));
        if (empty($adminEmail)) {
            Log::info("Skipping Admin Email for Order #{$order->order_number}: No admin email configured.");
            $this->recordNotification($order, OrderDeliveryNotification::CHANNEL_ADMIN_EMAIL, 'none', OrderDeliveryNotification::STATUS_SKIPPED, 'No admin email configured.');
            return false;
        }

        // Idempotency check: Don't resend if already sent
        if ($this->hasAlreadySent($order, OrderDeliveryNotification::CHANNEL_ADMIN_EMAIL)) {
            Log::info("Admin Email for Order #{$order->order_number} already sent. Skipping duplicate.");
            return true;
        }

        try {
            Mail::to($adminEmail)->send(new AdminNewOrderAlertMail($order, $adminOrderUrl, $storeInfo));

            $this->recordNotification(
                $order,
                OrderDeliveryNotification::CHANNEL_ADMIN_EMAIL,
                $adminEmail,
                OrderDeliveryNotification::STATUS_SENT
            );
            return true;
        } catch (Throwable $e) {
            Log::error("Admin Email dispatch failed for Order #{$order->order_number}: " . $e->getMessage(), ['exception' => $e]);
            $this->recordNotification(
                $order,
                OrderDeliveryNotification::CHANNEL_ADMIN_EMAIL,
                $adminEmail,
                OrderDeliveryNotification::STATUS_FAILED,
                $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Format WhatsApp confirmation message for customer.
     */
    public function formatCustomerWhatsAppMessage(Order $order, string $trackingUrl, array $storeInfo): string
    {
        $storeName = $storeInfo['name'] ?? 'FlavourFlow';
        $storePhone = $storeInfo['phone'] ?? '+91 99999 99999';
        $storeEmail = $storeInfo['email'] ?? 'support@flavourflow.com';

        $itemsList = '';
        foreach ($order->items as $item) {
            $pack = $item->unit ? " ({$item->unit})" : '';
            $itemsList .= "• {$item->quantity}x {$item->product_name}{$pack} - ₹" . number_format($item->total_price, 2) . "\n";
        }

        $addressSummary = "{$order->address}, {$order->city}, {$order->state} - {$order->pincode}";
        $totalFormatted = number_format($order->total_amount, 2);
        $orderDate = $order->created_at ? $order->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A');

        return "*Order Confirmed!* 🎉\n\n"
            . "Hi {$order->name},\n\n"
            . "Your {$storeName} order *#{$order->order_number}* has been placed and confirmed successfully.\n\n"
            . "📅 *Date:* {$orderDate}\n"
            . "💳 *Total:* ₹{$totalFormatted}\n"
            . "📦 *Status:* {$order->status}\n\n"
            . "*Items Ordered:*\n"
            . "{$itemsList}\n"
            . "📍 *Delivery Address:*\n"
            . "{$addressSummary}\n\n"
            . "🔗 *Track Your Order:*\n"
            . "{$trackingUrl}\n\n"
            . "📞 *Seller / Store Contact:*\n"
            . "{$storeName}\n"
            . "Phone: {$storePhone}\n"
            . "Email: {$storeEmail}\n\n"
            . "Thank you for shopping with {$storeName}!";
    }

    /**
     * Format WhatsApp alert message for Admin/Seller.
     */
    public function formatAdminWhatsAppMessage(Order $order, string $adminOrderUrl, array $storeInfo): string
    {
        $itemsList = '';
        foreach ($order->items as $item) {
            $itemsList .= "• {$item->quantity}x {$item->product_name} (₹" . number_format($item->total_price, 2) . ")\n";
        }

        $customerMobile = $order->mobile ?? 'Not provided';
        $totalFormatted = number_format($order->total_amount, 2);
        $addressSummary = "{$order->address}, {$order->city}, {$order->state} - {$order->pincode}";
        $orderDate = $order->created_at ? $order->created_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A');

        return "🚨 *NEW ORDER RECEIVED*\n\n"
            . "*Order #:* {$order->order_number}\n"
            . "*Date:* {$orderDate}\n"
            . "*Total:* ₹{$totalFormatted}\n"
            . "*Payment:* " . strtoupper($order->payment_method) . "\n"
            . "*Status:* {$order->status}\n\n"
            . "👤 *Customer Details:*\n"
            . "• Name: {$order->name}\n"
            . "• Phone: {$customerMobile}\n"
            . "• Email: {$order->email}\n\n"
            . "📦 *Products:*\n"
            . "{$itemsList}\n"
            . "📍 *Delivery Address:*\n"
            . "{$addressSummary}\n\n"
            . "🔗 *View in Admin:*\n"
            . "{$adminOrderUrl}";
    }

    /**
     * Check if a specific notification channel was already sent for this order.
     */
    protected function hasAlreadySent(Order $order, string $channel, string $type = 'order_confirmed'): bool
    {
        return OrderDeliveryNotification::where('order_id', $order->id)
            ->where('channel', $channel)
            ->where('type', $type)
            ->where('status', OrderDeliveryNotification::STATUS_SENT)
            ->exists();
    }

    /**
     * Record or update delivery notification state in database.
     */
    protected function recordNotification(
        Order $order,
        string $channel,
        string $recipient,
        string $status,
        ?string $errorMessage = null,
        array $payload = []
    ): void {
        try {
            OrderDeliveryNotification::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'type' => 'order_confirmed',
                    'channel' => $channel,
                ],
                [
                    'recipient' => $recipient,
                    'status' => $status,
                    'error_message' => $errorMessage,
                    'payload' => ! empty($payload) ? json_encode($payload) : null,
                    'sent_at' => $status === OrderDeliveryNotification::STATUS_SENT ? now() : null,
                ]
            );
        } catch (Throwable $e) {
            Log::error("Failed saving OrderDeliveryNotification record: " . $e->getMessage());
        }
    }
}
