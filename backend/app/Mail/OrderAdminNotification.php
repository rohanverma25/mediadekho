<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderAdminNotification extends Mailable
{
    use SerializesModels;

    public function __construct(public readonly Order $order)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Paid Order: '.$this->order->order_number);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order.admin', with: ['order' => $this->order]);
    }
}
