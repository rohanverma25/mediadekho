<?php

namespace App\Mail;

use App\Models\AwardNomination;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AwardNominationAdminNotification extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly AwardNomination $nomination)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Award Nomination: '.($this->nomination->award?->title ?? 'Untitled Award'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.award-nomination.admin', with: ['nomination' => $this->nomination]);
    }
}
