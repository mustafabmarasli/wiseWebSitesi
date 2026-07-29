<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Mail\AdminNewOrderMail;
use App\Mail\OrderConfirmedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Bir siparişi "ödendi" durumuna geçirir: stok düşer, kupon sayacı artar.
 *
 * Bu iş iki ayrı yerden tetiklenir — iyzico callback'i ve havale/EFT
 * siparişlerinde yöneticinin elle onayı. Mantık tek kopya olmasaydı
 * ikisinden biri eksik kalır ve stok tutmazdı.
 */
class OrderFulfiller
{
    /**
     * Siparişi ödendi olarak işaretler.
     *
     * IDEMPOTENT: sipariş zaten "beklemede" değilse hiçbir şey yapmaz ve
     * false döner. Callback iki kez tetiklenirse stok iki kez düşmez.
     *
     * @param  array<string, mixed>  $ekAlanlar  Duruma ek olarak kaydedilecek alanlar.
     */
    public function markPaid(Order $order, array $ekAlanlar = []): bool
    {
        if ($order->status !== OrderStatus::Pending->value) {
            return false;
        }

        $order->loadMissing('items');

        DB::transaction(function () use ($order, $ekAlanlar) {
            $order->update(array_merge([
                'status'               => OrderStatus::Paid->value,
                'payment_confirmed_at' => now(),
            ], $ekAlanlar));

            $this->stokDus($order);
            $this->kuponuIsle($order);
        });

        return true;
    }

    /**
     * Sipariş e-postalarını gönderir. Gönderim başarısız olsa bile sipariş
     * geçerlidir; bu yüzden hata yalnızca loglanır.
     */
    public function sendConfirmationMails(Order $order): void
    {
        try {
            Mail::to($order->email)->send(new OrderConfirmedMail($order));
            Mail::to(config('mail.admin_address'))->send(new AdminNewOrderMail($order));
        } catch (\Exception $e) {
            Log::error('Sipariş e-postası gönderilemedi (Sipariş ID: ' . $order->id . '): ' . $e->getMessage());
        }
    }

    /**
     * Stok düşümü koşullu ve atomiktir: iki müşteri son ürünü aynı anda satın
     * alırsa ikincisinin UPDATE'i 0 satır etkiler ve stok negatife düşmez.
     * Ödeme zaten alındığı için sipariş iptal edilmez, operasyon ekibinin
     * görebilmesi için loglanır.
     */
    private function stokDus(Order $order): void
    {
        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $affected = Product::where('id', $item->product_id)
                ->where('stock', '>=', $item->quantity)
                ->decrement('stock', $item->quantity);

            if ($affected === 0) {
                Log::warning('Stok yetersiz kaldı, sipariş fazla satış içeriyor', [
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                ]);
            }
        }
    }

    /**
     * Kupon satırı kilitlenir: iki ödeme aynı anda işlenirse ikincisi bekler
     * ve güncel sayacı görür. Kilitsiz increment'te ikisi de eski değeri
     * okuyup limiti aşabiliyordu.
     */
    private function kuponuIsle(Order $order): void
    {
        if (! $order->coupon_code) {
            return;
        }

        $coupon = Coupon::where('code', $order->coupon_code)->lockForUpdate()->first();

        if (! $coupon) {
            return;
        }

        if ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
            // Ödeme tahsil edildiği için sipariş iptal edilmez;
            // operasyonun görebilmesi için kaydedilir.
            Log::warning('Kupon kullanım limiti aşıldı', [
                'order_id'   => $order->id,
                'coupon'     => $coupon->code,
                'max_uses'   => $coupon->max_uses,
                'used_count' => $coupon->used_count,
            ]);
        }

        $coupon->increment('used_count');
    }
}
