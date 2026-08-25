<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobApplicationAdminNotification extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly JobApplication $application)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Job Application: '.($this->application->job?->title ?? 'Untitled Role'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.job-application.admin', with: ['application' => $this->application]);
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->application->resume) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('public', $this->application->resume)
                ->as($this->application->resume_original_name ?: 'resume'),
        ];
    }
}
