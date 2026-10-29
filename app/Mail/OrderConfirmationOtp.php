<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationOtp extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $otp
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Order Confirmation Code: '.$this->otp.' - FlavourFlow',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-confirmation-otp',
            with: [
                'order' => $this->order->loadMissing(['items', 'payments']),
                'otp' => $this->otp,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
