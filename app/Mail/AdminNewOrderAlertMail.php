<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewOrderAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $adminOrderUrl,
        public array $storeInfo = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🚨 [New Order Alert] Order #{$this->order->order_number} Received (₹{$this->order->total_amount})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.admin_new_order',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
