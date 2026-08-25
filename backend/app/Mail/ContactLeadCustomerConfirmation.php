<?php

namespace App\Mail;

use App\Models\ContactLead;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactLeadCustomerConfirmation extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly ContactLead $lead)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "We've received your enquiry");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-lead.customer', with: ['lead' => $this->lead]);
    }
}
