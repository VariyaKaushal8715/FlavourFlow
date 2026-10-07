<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminOrderCancelledNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Admin Alert: Order #'.$this->order->order_number.' Cancelled',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-order-cancelled',
            with: [
                'order' => $this->order->loadMissing('items'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
