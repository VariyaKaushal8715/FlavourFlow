<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminOrderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function __construct(
        public string $event,
        public string $headline,
        public string $intro,
        public ?Order $order = null,
        public ?Payment $payment = null,
        public ?User $user = null
    ) {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        $identifier = $this->order ? ' - '.$this->order->order_number : '';

        return new Envelope(
            subject: $this->headline.$identifier
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-order'
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Admin email failed', [
            'event' => $this->event,
            'order_id' => $this->order?->id,
            'order_number' => $this->order?->order_number,
            'payment_id' => $this->payment?->id,
            'user_id' => $this->user?->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
