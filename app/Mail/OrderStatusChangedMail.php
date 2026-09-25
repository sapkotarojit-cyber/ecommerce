<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public string $oldStatus;

    public string $newStatus;

    public function __construct(
        Order $order,
        string $oldStatus,
        string $newStatus
    ) {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;

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
            subject: 'Order Status Updated - ' .
                $this->order->tracking_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status-changed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}