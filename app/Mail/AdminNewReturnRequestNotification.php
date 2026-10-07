<?php

namespace App\Mail;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewReturnRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ReturnRequest $returnRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📦 Admin Alert: New Return Request for Order #'.$this->returnRequest->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin-new-return-request',
            with: [
                'returnRequest' => $this->returnRequest->loadMissing('order'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
