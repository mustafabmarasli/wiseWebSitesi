<?php

namespace App\Mail;

use App\Models\Campaign;
use App\Models\MarketingConsent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  MarketingConsent|null  $consent  Deneme gönderiminde null olur;
     *   o zaman çıkış bağlantısı yerine "deneme" notu basılır.
     */
    public function __construct(
        public Campaign $campaign,
        public ?MarketingConsent $consent = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: ($this->consent ? '' : '[DENEME] ') . ($this->campaign->subject ?: $this->campaign->title),
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.marketing.campaign');
    }
}
