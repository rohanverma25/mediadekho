<?php

namespace App\Mail;

use App\Models\ContactLead;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactLeadAdminNotification extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly ContactLead $lead)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Enquiry: '.($this->lead->subject ?: 'General Enquiry'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-lead.admin', with: ['lead' => $this->lead]);
    }
}
