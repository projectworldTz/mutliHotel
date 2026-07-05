<?php

namespace App\Mail;

use App\Models\Hotel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HotelSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Hotel $hotel) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Hotel Pending Approval — ' . $this->hotel->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.hotel-submitted',
        );
    }
}
