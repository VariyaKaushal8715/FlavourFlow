<?php

namespace App\Services;

use App\Mail\OrderConfirmationOtp;
use App\Models\Order;
use DomainException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderOtpService
{
    /**
     * Expiration duration in minutes for order confirmation OTPs.
     */
    public const OTP_EXPIRATION_MINUTES = 15;

    /**
     * Generate and dispatch order confirmation OTP to customer email.
     *
     * STRICT SECURITY GATE:
     * This method strictly ensures that an order OTP is NEVER generated or dispatched
     * unless the order has a genuinely verified and confirmed payment.
     *
     * @throws DomainException if payment is unverified or order is in an invalid state.
     */
    public function generateAndSendOrderOtp(Order $order): bool
    {
        // 1. Strict Payment Gate: Order must have verified payment / confirmed state
        $isPaymentConfirmed = ($order->payment_status === 'paid' || $order->status === 'Confirmed');

        // Also inspect payment record
        $latestPayment = $order->payments()->latest()->first();
        $isCaptured = $latestPayment && in_array($latestPayment->status, ['captured', 'successful'], true);

        if (! $isPaymentConfirmed && ! $isCaptured) {
            Log::warning('OTP dispatch blocked: payment is not verified', [
                'order_id' => $order->id,
                'order_status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_record_status' => $latestPayment?->status,
            ]);

            throw new DomainException('Cannot generate or send order confirmation OTP: payment is not yet verified.');
        }

        // 2. Prevent spam: do not resend if OTP was sent within the last 60 seconds
        if ($order->otp_sent_at && $order->otp_sent_at->diffInSeconds(now()) < 60 && ! empty($order->order_confirmation_otp)) {
            Log::info('Order OTP rate limit: re-sending throttled', [
                'order_id' => $order->id,
                'seconds_since_last_send' => $order->otp_sent_at->diffInSeconds(now()),
            ]);

            return true;
        }

        // 3. Generate secure 6-digit numeric OTP
        $otp = (string) random_int(100000, 999999);

        // 4. Update order record
        $order->update([
            'order_confirmation_otp' => $otp,
            'otp_expires_at' => now()->addMinutes(self::OTP_EXPIRATION_MINUTES),
            'otp_sent_at' => now(),
        ]);

        // 5. Dispatch email
        try {
            Mail::to($order->email)->send(new OrderConfirmationOtp($order, $otp));

            Log::info('Order confirmation OTP dispatched successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'email' => $order->email,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send order confirmation OTP email: '.$e->getMessage(), [
                'order_id' => $order->id,
            ]);

            return false;
        }
    }

    /**
     * Validate submitted OTP against order record.
     */
    public function verifyOtp(Order $order, string $inputOtp): bool
    {
        $trimmed = trim($inputOtp);

        if (empty($trimmed) || empty($order->order_confirmation_otp)) {
            return false;
        }

        if ($order->otp_expires_at && $order->otp_expires_at->isPast()) {
            Log::notice('Order OTP verification failed: code has expired', [
                'order_id' => $order->id,
            ]);

            return false;
        }

        if (hash_equals((string) $order->order_confirmation_otp, $trimmed)) {
            $order->update([
                'otp_verified_at' => now(),
            ]);

            Log::info('Order OTP verified successfully', [
                'order_id' => $order->id,
            ]);

            return true;
        }

        return false;
    }
}
