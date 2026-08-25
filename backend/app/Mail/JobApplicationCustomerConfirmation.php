<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobApplicationCustomerConfirmation extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly JobApplication $application)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "We've received your application");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.job-application.customer', with: ['application' => $this->application]);
    }
}
