<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCustomerAdminNotification extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Customer Registered: '.$this->user->name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.welcome.admin', with: ['user' => $this->user]);
    }
}
