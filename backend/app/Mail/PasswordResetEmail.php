<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetEmail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $resetUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Reset Your '.config('app.name').' Password');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.password-reset.customer', with: [
            'user' => $this->user,
            'resetUrl' => $this->resetUrl,
        ]);
    }
}
