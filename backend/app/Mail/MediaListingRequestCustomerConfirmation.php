<?php

namespace App\Mail;

use App\Models\MediaListingRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MediaListingRequestCustomerConfirmation extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly MediaListingRequest $mediaListingRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "We've received your media listing request");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.media-listing-request.customer', with: ['mediaListingRequest' => $this->mediaListingRequest]);
    }
}
