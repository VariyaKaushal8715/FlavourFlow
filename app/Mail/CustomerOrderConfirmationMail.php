<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerOrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $trackingUrl,
        public array $storeInfo = []
    ) {}

    public function envelope(): Envelope
    {
        $storeName = $this->storeInfo['name'] ?? config('order_notifications.store.name', 'FlavourFlow');

        return new Envelope(
            subject: "Order Confirmed! #{$this->order->order_number} - {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.customer_confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
