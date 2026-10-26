<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

class DemoPaymentService
{
    public const DEMO_NOTICE = 'DEMO — NO REAL PAYMENT';

    public function __construct(
        protected OrderFulfillmentService $fulfillmentService
    ) {}

    /**
     * Demo mode requires zero external credentials or network connections.
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * Create an initial simulated payment record for an order.
     */
    public function initializeDemoPayment(Order $order): Payment
    {
        $demoTxnId = 'DEMO-TXN-'.strtoupper(Str::random(12));
        $demoOrderId = 'DEMO-ORD-'.$order->order_number;

        return Payment::updateOrCreate(
            ['order_id' => $order->id, 'provider' => 'demo'],
            [
                'user_id' => $order->user_id,
                'is_demo' => true,
                'provider' => 'demo',
                'payment_method' => 'demo',
                'transaction_id' => $demoTxnId,
                'gateway_order_id' => $demoOrderId,
                'amount' => $order->total_amount,
                'currency' => 'INR',
                'status' => 'created',
                'payment_details' => [
                    'demo_notice' => self::DEMO_NOTICE,
                    'is_simulated' => true,
                    'simulated_gateway' => 'FlavourFlow Demo Simulator',
                    'timestamp' => now()->toIso8601String(),
                ],
            ]
        );
    }

    /**
     * Process simulated payment outcome (success or failure) without touching any external gateway.
     *
     * @return array{success: bool, is_demo: bool, message: string, redirect_url?: string, status?: string}
     */
    public function processOutcome(Order $order, string $outcome = 'success'): array
    {
        $payment = Payment::where('order_id', $order->id)->where('is_demo', true)->latest()->first();

        if (! $payment) {
            $payment = $this->initializeDemoPayment($order);
        }

        // Tag order as demo explicitly
        $order->update([
            'is_demo' => true,
            'payment_method' => 'demo',
        ]);

        if ($outcome === 'failure') {
            $payment->update([
                'status' => 'failed',
                'failure_reason' => 'Simulated payment declined by user in Demo Mode (DEMO — NO REAL PAYMENT).',
                'response_data' => [
                    'demo_notice' => self::DEMO_NOTICE,
                    'is_simulated' => true,
                    'simulated_outcome' => 'failure',
                    'verified_by_real_gateway' => false,
                    'simulated_at' => now()->toIso8601String(),
                ],
            ]);

            return [
                'success' => false,
                'is_demo' => true,
                'gateway' => 'demo',
                'status' => 'failed',
                'message' => 'Simulated demo payment failed (DEMO — NO REAL PAYMENT). Cart items are preserved for retry.',
            ];
        }

        // Fulfill the order idempotently using OrderFulfillmentService
        $this->fulfillmentService->fulfill(
            $order,
            $payment,
            $payment->transaction_id,
            [
                'demo_notice' => self::DEMO_NOTICE,
                'is_simulated' => true,
                'simulated_gateway' => 'FlavourFlow Demo Simulator',
                'simulated_outcome' => 'success',
                'verified_by_real_gateway' => false,
                'simulated_at' => now()->toIso8601String(),
            ]
        );

        $payment->refresh();
        $payment->update([
            'status' => 'captured',
            'is_demo' => true,
            'paid_at' => now(),
        ]);

        return [
            'success' => true,
            'is_demo' => true,
            'gateway' => 'demo',
            'status' => 'captured',
            'message' => 'Simulated demo payment completed successfully (DEMO — NO REAL PAYMENT).',
            'redirect_url' => route('checkout.success'),
        ];
    }
}
