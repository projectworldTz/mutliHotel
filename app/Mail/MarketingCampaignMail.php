<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MarketingCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly EmailCampaign $campaign,
        public readonly User $recipient,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaign->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.marketing-campaign',
        );
    }
}
