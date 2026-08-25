<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeCustomerEmail extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Welcome to '.config('app.name').'!');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.welcome.customer', with: ['user' => $this->user]);
    }
}
