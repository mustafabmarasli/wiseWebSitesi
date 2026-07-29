<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(\App\Models\Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Konu satırı ödemenin alınıp alınmadığını söylemeli: havale
        // siparişlerinde para henüz gelmemiştir, "Yeni sipariş" başlığı
        // yanıltıcı olur ve kargoya erken verilmesine yol açabilir.
        $konu = $this->order->status === \App\Enums\OrderStatus::Pending->value
            ? 'ÖDEME BEKLENİYOR — Yeni Sipariş ' . $this->order->display_number
            : 'Yeni Sipariş Alındı! - ' . $this->order->display_number;

        return new Envelope(subject: $konu);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.new_order_admin',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
