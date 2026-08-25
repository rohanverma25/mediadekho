<?php

namespace App\Mail;

use App\Models\AwardNomination;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AwardNominationCustomerConfirmation extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly AwardNomination $nomination)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "We've received your award nomination");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.award-nomination.customer', with: ['nomination' => $this->nomination]);
    }
}
