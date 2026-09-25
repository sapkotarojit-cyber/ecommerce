<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;

        $this->order->load([
            'user',
            'dokan',
            'shippingAddress',
            'orderItems.product',
            'orderItems.varient',
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Placed Successfully - ' .
                $this->order->tracking_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-placed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}