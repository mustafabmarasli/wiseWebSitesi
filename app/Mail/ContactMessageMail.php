<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'İletişim Formu: ' . $this->contactMessage->subject,
            // Gönderen daima kendi doğrulanmış adresimiz olmalı; ziyaretçinin
            // adresinden göndermek SPF/DKIM'i bozar ve spam'e düşürür.
            // Ziyaretçinin adresi yanıt adresi olarak eklenir.
            replyTo: [new Address($this->contactMessage->email, $this->contactMessage->name)],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact.message',
        );
    }
}
