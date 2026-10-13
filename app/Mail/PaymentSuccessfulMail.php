<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class PaymentSuccessfulMail extends Mailable
{
    use Queueable, SerializesModels;

    public Collection $orders;

    public function __construct(Collection $orders)
    {
        $this->orders = $orders;

        $this->orders->each(function ($order) {
            $order->loadMissing([
                'user',
                'dokan',
                'shippingAddress',
                'orderItems.product',
                'orderItems.varient',
            ]);
        });
    }

    public function envelope(): Envelope
    {
        $firstOrder = $this->orders->first();

        return new Envelope(
            subject: 'Payment Successful - ' .
                ($firstOrder?->tracking_number ?? 'Your Order'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-successful',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
