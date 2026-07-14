<?php

namespace App\Mail;

use App\Models\SatisfactionSurvey;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestSurveyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly SatisfactionSurvey $survey) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'How was your stay at ' . $this->survey->hotel->name . '?',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.guest-survey',
        );
    }
}
