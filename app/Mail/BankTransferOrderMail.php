<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Havale/EFT ile verilen sipariş sonrası müşteriye giden banka bilgileri.
 *
 * Sipariş henüz ödenmedi; bu e-posta müşterinin ödemeyi yapabilmesi için
 * hesap bilgilerini ve açıklamaya yazılacak sipariş numarasını taşır.
 */
class BankTransferOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public Setting $setting,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Siparişinizi Aldık — Ödeme Bilgileri ' . $this->order->display_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.bank_transfer',
        );
    }
}
