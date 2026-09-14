<?php

namespace App\Services;

use App\Mail\AccountCreatedMail;
use App\Mail\AdminOrderMail;
use App\Mail\OrderCustomerMail;
use App\Models\AdminProfile;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailNotificationService
{
    /**
     * Dynamically retrieve the admin email from the database (User/AdminProfile).
     */
    public function resolveAdminEmail(): ?string
    {
        $adminUser = User::where('is_admin', true)->first();
        if ($adminUser) {
            $profileEmail = $adminUser->adminProfile?->email;
            if (! empty($profileEmail)) {
                return $profileEmail;
            }
            if (! empty($adminUser->email)) {
                return $adminUser->email;
            }
        }

        $adminProfile = AdminProfile::whereNotNull('email')->where('email', '!=', '')->first();
        if ($adminProfile && ! empty($adminProfile->email)) {
            return $adminProfile->email;
        }

        return config('admin.email');
    }

    public function sendAccountCreated(User $user): void
    {
        $this->sendToUser($user, new AccountCreatedMail($user), 'account_created', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function sendOrderPlaced(Order $order): void
    {
        $order = $this->loadOrder($order);

        $this->sendToOrderCustomer($order, new OrderCustomerMail(
            order: $order,
            event: 'order_placed',
            headline: 'Order placed',
            intro: 'We have received your order and will keep you posted as it moves ahead.'
        ), 'order_placed');

        // Admin Event 1: New Order Received
        $this->sendAdmin(new AdminOrderMail(
            event: 'new_order',
            headline: 'New order received',
            intro: "{$order->name} placed a new {$order->payment_method} order.",
            order: $order,
            payment: $order->payments->first()
        ), 'new_order', ['order_id' => $order->id]);
    }

    public function sendPaymentSuccessful(Payment $payment): void
    {
        $payment->loadMissing('order.items.product', 'order.user');
        $order = $this->loadOrder($payment->order);

        $this->sendToOrderCustomer($order, new OrderCustomerMail(
            order: $order,
            event: 'payment_successful',
            headline: 'Payment successful',
            intro: 'Your payment was received successfully and your order is confirmed.'
        ), 'payment_successful');
    }

    public function sendOrderStatusChanged(Order $order): void
    {
        $order = $this->loadOrder($order);
        $status = $order->status;

        $messages = [
            'Confirmed' => ['Order confirmed', 'Your order has been confirmed and is being prepared.'],
            'Shipped' => ['Order shipped', 'Your order has been shipped.'],
            'Out for Delivery' => ['Out for delivery', 'Your order is out for delivery.'],
            'Delivered' => ['Order delivered', 'Your order has been delivered.'],
            'Cancelled' => ['Order cancelled', 'Your order has been cancelled.'],
        ];

        if (! isset($messages[$status])) {
            return;
        }

        [$headline, $intro] = $messages[$status];

        // Customer email for all status changes
        $this->sendToOrderCustomer($order, new OrderCustomerMail(
            order: $order,
            event: 'order_status_'.$this->eventKey($status),
            headline: $headline,
            intro: $intro
        ), 'order_status_'.$this->eventKey($status));

        // Admin Event 4: Order Cancellation ONLY
        if ($status === 'Cancelled') {
            $reason = $order->cancellation_reason ? " Reason: {$order->cancellation_reason}" : '';

            $this->sendAdmin(new AdminOrderMail(
                event: 'order_cancelled',
                headline: 'Order cancelled',
                intro: "Order #{$order->order_number} has been cancelled.{$reason}",
                order: $order,
                payment: $order->payments->first()
            ), 'order_cancelled', ['order_id' => $order->id]);
        }
    }

    // Admin Event 2: Refund Request / Refund Event
    public function sendRefundRequested(RefundRequest $refundRequest): void
    {
        $order = $this->loadOrder($refundRequest->order);

        $reason = $refundRequest->reason ? " Reason: {$refundRequest->reason}" : '';

        $this->sendAdmin(new AdminOrderMail(
            event: 'refund_requested',
            headline: 'Refund request received',
            intro: 'A refund request of Rs. '.number_format((float) $refundRequest->amount, 2)." was submitted for Order #{$order->order_number}.{$reason}",
            order: $order,
            payment: $order->payments->first()
        ), 'refund_requested', [
            'order_id' => $order->id,
            'refund_request_id' => $refundRequest->id,
        ]);
    }

    public function sendRefundStatusUpdated(RefundRequest $refundRequest): void
    {
        $order = $this->loadOrder($refundRequest->order);

        $this->sendAdmin(new AdminOrderMail(
            event: 'refund_status_updated',
            headline: "Refund request {$refundRequest->status}",
            intro: "Refund request for Order #{$order->order_number} was marked as {$refundRequest->status}.",
            order: $order,
            payment: $order->payments->first()
        ), 'refund_status_updated', [
            'order_id' => $order->id,
            'refund_request_id' => $refundRequest->id,
        ]);
    }

    // Admin Event 3: Return Request / Return Event
    public function sendReturnRequested(ReturnRequest $returnRequest): void
    {
        $order = $this->loadOrder($returnRequest->order);

        $reason = $returnRequest->reason ? " Reason: {$returnRequest->reason}" : '';

        $this->sendAdmin(new AdminOrderMail(
            event: 'return_requested',
            headline: 'Return request received',
            intro: "A return request was submitted for Order #{$order->order_number}.{$reason}",
            order: $order,
            payment: $order->payments->first()
        ), 'return_requested', [
            'order_id' => $order->id,
            'return_request_id' => $returnRequest->id,
        ]);
    }

    public function sendReturnStatusUpdated(ReturnRequest $returnRequest): void
    {
        $order = $this->loadOrder($returnRequest->order);

        $this->sendAdmin(new AdminOrderMail(
            event: 'return_status_updated',
            headline: "Return request {$returnRequest->status}",
            intro: "Return request for Order #{$order->order_number} was marked as {$returnRequest->status}.",
            order: $order,
            payment: $order->payments->first()
        ), 'return_status_updated', [
            'order_id' => $order->id,
            'return_request_id' => $returnRequest->id,
        ]);
    }

    private function loadOrder(Order $order): Order
    {
        return $order->loadMissing(['items.product', 'user', 'payments']);
    }

    private function sendToOrderCustomer(Order $order, Mailable $mailable, string $event): void
    {
        if (! $order->email) {
            Log::warning('Customer order email skipped because order email is missing', [
                'order_id' => $order->id,
                'event' => $event,
            ]);

            return;
        }

        $this->send($order->email, $mailable, $event, [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'email' => $order->email,
        ]);
    }

    private function sendToUser(User $user, Mailable $mailable, string $event, array $context): void
    {
        if (! $user->email) {
            Log::warning('User email skipped because email is missing', $context + ['event' => $event]);

            return;
        }

        $this->send($user->email, $mailable, $event, $context);
    }

    private function sendAdmin(Mailable $mailable, string $event, array $context): void
    {
        $adminEmail = $this->resolveAdminEmail();

        if (! $adminEmail) {
            Log::warning('Admin email skipped because admin email is not configured or found', $context + ['event' => $event]);

            return;
        }

        $this->send($adminEmail, $mailable, $event, $context + ['email' => $adminEmail]);
    }

    private function send(string $email, Mailable $mailable, string $event, array $context): void
    {
        try {
            Mail::to($email)->queue($mailable);

            Log::info('Email notification queued', $context + ['event' => $event]);
        } catch (Throwable $exception) {
            Log::error('Email notification could not be queued', $context + [
                'event' => $event,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function eventKey(string $value): string
    {
        return str($value)->lower()->replace(' ', '_')->toString();
    }
}
