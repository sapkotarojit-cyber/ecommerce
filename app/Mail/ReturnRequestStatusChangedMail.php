<?php

namespace App\Mail;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnRequestStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ReturnRequest $returnRequest,
        public string $oldStatus,
        public string $newStatus,
        public string $oldRefundStatus,
        public string $newRefundStatus,
        public bool $returnStatusChanged,
        public bool $refundStatusChanged,
    ) {
    }

    public function envelope(): Envelope
    {
        $orderNumber = $this->returnRequest->order?->id
            ?? $this->returnRequest->order_id;

        return new Envelope(
            subject: 'Return & Refund Status Updated - Order #' . $orderNumber . ' - EmpireInnovation',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails/return-request-status-changed',
        );
    }
}