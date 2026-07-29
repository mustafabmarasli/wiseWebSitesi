<?php

namespace App\Services;

use App\Mail\BackInStockMail;
use App\Models\Product;
use App\Models\StockNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Stoğu yeniden dolan ürünü bekleyenlere haber verir.
 *
 * Tetikleyici `ProductObserver`'dır: stok 0'dan (veya altından) yukarı
 * çıktığı anda çalışır. Panelden elle düzeltme, Excel içe aktarma ve
 * toplu güncelleme — hepsi model üzerinden kaydettiği için aynı yoldan geçer.
 */
class StockNotifier
{
    /**
     * Bekleyen kayıtlara e-posta gönderir, gidenleri işaretler.
     *
     * @return int  Başarıyla gönderilen e-posta sayısı.
     */
    public function notifyWaiting(Product $product): int
    {
        $gonderilen = 0;

        StockNotification::where('product_id', $product->id)
            ->whereNull('notified_at')
            ->chunkById(100, function ($bildirimler) use ($product, &$gonderilen) {
                foreach ($bildirimler as $bildirim) {
                    if ($this->gonder($product, $bildirim)) {
                        $gonderilen++;
                    }
                }
            });

        return $gonderilen;
    }

    /**
     * Tek kaydı gönderir.
     *
     * Gönderilemeyen kayıt BEKLEYEN OLARAK KALIR — `notified_at` yalnızca
     * e-posta gerçekten çıktığında dolar. Aksi halde SMTP kesintisinde
     * müşteri hiçbir zaman haber almadığı hâlde panelde "bildirildi"
     * görünürdü ve kimse fark etmezdi.
     */
    private function gonder(Product $product, StockNotification $bildirim): bool
    {
        try {
            Mail::to($bildirim->email)->send(new BackInStockMail($product));
        } catch (\Exception $e) {
            Log::error('Stok bildirimi gönderilemedi', [
                'product_id' => $product->id,
                'email'      => $bildirim->email,
                'hata'       => $e->getMessage(),
            ]);

            return false;
        }

        $bildirim->forceFill(['notified_at' => now()])->save();

        return true;
    }
}
