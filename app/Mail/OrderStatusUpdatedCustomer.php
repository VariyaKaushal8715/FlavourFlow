<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $status
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->status) {
            'Shipped' => 'Your Order #'.$this->order->order_number.' Has Been Shipped - FlavourFlow',
            'Out for Delivery' => 'Your Order #'.$this->order->order_number.' Is Out for Delivery - FlavourFlow',
            'Delivered' => 'Your Order #'.$this->order->order_number.' Has Been Delivered - FlavourFlow',
            'Cancelled' => 'Order #'.$this->order->order_number.' Cancellation Notice - FlavourFlow',
            default => 'Update on Order #'.$this->order->order_number.' - FlavourFlow',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-status-customer',
            with: [
                'order' => $this->order->loadMissing('items'),
                'status' => $this->status,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
