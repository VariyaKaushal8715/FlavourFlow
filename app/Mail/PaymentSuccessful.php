<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessful extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Successful for Order #'.$this->order->order_number.' - FlavourFlow',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-successful',
            with: [
                'order' => $this->order->loadMissing(['items', 'payment']),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
