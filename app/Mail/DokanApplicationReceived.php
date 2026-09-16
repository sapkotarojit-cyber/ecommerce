<?php

namespace App\Mail;

use App\Models\Dokan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DokanApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public Dokan $dokan;

    public function __construct(Dokan $dokan)
    {
        $this->dokan = $dokan;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vendor Application Received - Empireinnovation',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.dokan_application_received',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

