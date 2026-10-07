<?php

namespace App\Mail;

use App\Models\RefundRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundSuccessfulCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RefundRequest $refundRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Refund Processed for Order #'.$this->refundRequest->order->order_number.' - FlavourFlow',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.refund-successful-customer',
            with: [
                'refundRequest' => $this->refundRequest->loadMissing('order'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
