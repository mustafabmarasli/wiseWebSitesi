<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Mail\OrderShippedMail;
use App\Mail\ReviewInviteMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    /**
     * İki bildirim durum geçişine bağlıdır: "Kargoya Verildi" ve
     * "Teslim Edildi". İkisi de bir kez gider — sipariş tekrar düzenlense
     * (ör. takip linkini sonradan eklemek) ikinci e-posta gitmez.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $this->bildirKargoyaVerildi($order);
        $this->bildirYorumDaveti($order);
    }

    /**
     * Takip numarası girilmişse e-postada gösterilir, girilmemişse bile
     * yola çıktığı bilgisi gider — takip numarasını beklemek bildirimi
     * geciktirmemeli.
     */
    private function bildirKargoyaVerildi(Order $order): void
    {
        if ($order->status !== OrderStatus::Shipped->value || $order->shipped_notified_at !== null) {
            return;
        }

        // E-posta hatası siparişi düşürmemeli: durum zaten kaydedildi.
        try {
            Mail::to($order->email)->send(new OrderShippedMail($order));
        } catch (\Exception $e) {
            Log::error('Kargo bildirimi gönderilemedi', [
                'order_id' => $order->id,
                'hata'     => $e->getMessage(),
            ]);

            return;
        }

        // saveQuietly: observer'ı tekrar tetiklememek için.
        $order->forceFill(['shipped_notified_at' => now()])->saveQuietly();
    }

    /**
     * Teslim edildiğinde yorum daveti gider. Yalnızca ÜYE siparişleri için:
     * yorum yazmak giriş gerektiriyor, misafir siparişine davet gitse de
     * müşteri yorum yazamaz.
     */
    private function bildirYorumDaveti(Order $order): void
    {
        if ($order->status !== OrderStatus::Delivered->value || $order->review_invite_sent_at !== null) {
            return;
        }

        if ($order->user_id === null) {
            return;
        }

        try {
            Mail::to($order->email)->send(new ReviewInviteMail($order));
        } catch (\Exception $e) {
            Log::error('Yorum daveti gönderilemedi', [
                'order_id' => $order->id,
                'hata'     => $e->getMessage(),
            ]);

            return;
        }

        $order->forceFill(['review_invite_sent_at' => now()])->saveQuietly();
    }
}
