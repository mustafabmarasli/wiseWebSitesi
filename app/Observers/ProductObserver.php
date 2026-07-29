<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\StockNotifier;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    /**
     * Stok 0'dan yukarı çıktığında bekleyenlere haber verilir.
     *
     * DİKKAT: Bu yalnızca model üzerinden yapılan kayıtlarda çalışır.
     * `Product::where(...)->update()` gibi sorgu kurucu güncellemeleri
     * model olayı üretmez. Stok artışını bir yerde sorgu kurucuyla
     * yaparsan bildirim gitmez — `StockNotifier`'ı orada elle çağır.
     *
     * (`OrderFulfiller` stoğu yalnızca DÜŞÜRÜR, bu yüzden orada sorun yok.)
     */
    public function updated(Product $product): void
    {
        if (! $product->wasChanged('stock')) {
            return;
        }

        $onceki = (int) $product->getOriginal('stock');

        if ($onceki > 0 || $product->stock <= 0) {
            return;
        }

        // Bildirim gönderimi ürün kaydetmeyi ASLA düşürmemeli: yönetici
        // stoğu girdi, iş bitti. E-posta katmanındaki hata loglanır.
        try {
            (new StockNotifier())->notifyWaiting($product);
        } catch (\Exception $e) {
            Log::error('Stok bildirimleri işlenemedi', [
                'product_id' => $product->id,
                'hata'       => $e->getMessage(),
            ]);
        }
    }
}
