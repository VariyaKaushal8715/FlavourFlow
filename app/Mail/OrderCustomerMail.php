<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderCustomerMail extends Mailable implements ShouldQueue
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
        public Order $order,
        public string $event,
        public string $headline,
        public string $intro
    ) {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->headline.' - '.$this->order->order_number
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-customer'
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Customer order email failed', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'event' => $this->event,
            'email' => $this->order->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
