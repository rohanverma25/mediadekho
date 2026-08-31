<?php

namespace App\Mail;

use App\Models\MediaListingRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MediaListingRequestAdminNotification extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly MediaListingRequest $mediaListingRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Media Listing Request: '.$this->mediaListingRequest->media_title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.media-listing-request.admin', with: ['mediaListingRequest' => $this->mediaListingRequest]);
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->mediaListingRequest->media_kit) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('public', $this->mediaListingRequest->media_kit)
                ->as($this->mediaListingRequest->media_kit_original_name ?: 'media-kit'),
        ];
    }
}
