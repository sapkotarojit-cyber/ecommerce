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

    public ReturnRequest $returnRequest;

    public string $oldReturnStatus;
    public string $newReturnStatus;

    public string $oldRefundStatus;
    public string $newRefundStatus;

    public function __construct(
        ReturnRequest $returnRequest,
        string $oldReturnStatus,
        string $newReturnStatus,
        string $oldRefundStatus,
        string $newRefundStatus,
    ) {
        $this->returnRequest = $returnRequest;

        $this->oldReturnStatus = $oldReturnStatus;
        $this->newReturnStatus = $newReturnStatus;

        $this->oldRefundStatus = $oldRefundStatus;
        $this->newRefundStatus = $newRefundStatus;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Return & Refund Status Updated - Order #' .
                ($this->returnRequest->order?->id ?? $this->returnRequest->order_id) .
                ' - EmpireInnovation',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.return-request-status-changed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}