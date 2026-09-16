<?php

namespace App\Mail;

use App\Models\Dokan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DokanRequestRejection extends Mailable
{
    use Queueable, SerializesModels;

    public Dokan $dokan;
    public string $comment;

    public function __construct(Dokan $dokan, string $comment)
    {
        $this->dokan = $dokan;
        $this->comment = $comment;
    }

    public function build()
    {
        return $this
            ->subject('Vendor Application Rejected - Empireinnovation')
            ->view('mail.dokan_request_rejection');
    }
}