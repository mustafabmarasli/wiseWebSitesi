<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductViewRecorder
{
    /**
     * Aynı ziyaretçinin aynı ürünü bu süre içinde tekrar açması tek görüntülenme sayılır.
     */
    private const DEDUPE_MINUTES = 30;

    private const SESSION_KEY = 'viewed_products';

    /**
     * "Son Görüntülenen Ürünler" rafı için tutulan liste — analitik sayımdan
     * (yukarıdaki DEDUPE_MINUTES) BAĞIMSIZDIR. Sayım tekrar tıklamaları
     * saymamak içindir; bu raf ise ziyaretçi aynı ürüne 5 dakika sonra tekrar
     * baksa bile en üstte kalmalı, oturum boyunca geçerli olmalı.
     */
    private const RECENT_SESSION_KEY = 'recently_viewed_products';

    private const RECENT_LIMIT = 12;

    /**
     * Ürün görüntülenmesini kaydeder.
     *
     * Sayfa yenilemeleri ve ileri/geri gezinmeleri saymamak için oturum bazlı
     * tekilleştirme yapılır; aksi halde rakamlar gerçeği yansıtmaz.
     */
    public function record(Product $product, Request $request): void
    {
        // "Son görüntülenenler" rafı, analitik sayımdan bağımsız olarak HER
        // ziyarette güncellenir — sayfa yenilemesi bile ürünü rafta en üste
        // taşımalı, dedupe penceresi burada geçerli değil.
        $this->rememberRecentlyViewed($product);

        if ($this->wasCountedRecently($product)) {
            return;
        }

        $this->markCounted($product);

        try {
            ProductView::create([
                'product_id'   => $product->id,
                'user_id'      => auth()->id(),
                'visitor_hash' => hash('sha256', $request->ip() . '|' . $request->userAgent()),
                'created_at'   => now(),
            ]);

            // Sayaç atomik artırılır; eşzamanlı ziyaretlerde kayıp olmaz.
            Product::where('id', $product->id)->increment('view_count');
        } catch (\Throwable $e) {
            // Görüntülenme kaydı, ürün sayfasının açılmasını asla engellememeli.
            report($e);
        }
    }

    private function wasCountedRecently(Product $product): bool
    {
        $viewed = session()->get(self::SESSION_KEY, []);
        $seenAt = $viewed[$product->id] ?? null;

        return $seenAt !== null && $seenAt > now()->subMinutes(self::DEDUPE_MINUTES)->timestamp;
    }

    private function markCounted(Product $product): void
    {
        $viewed = session()->get(self::SESSION_KEY, []);
        $cutoff = now()->subMinutes(self::DEDUPE_MINUTES)->timestamp;

        // Süresi geçmiş kayıtları at, oturum sonsuza kadar büyümesin
        $viewed = array_filter($viewed, fn ($ts) => $ts > $cutoff);

        $viewed[$product->id] = now()->timestamp;

        session()->put(self::SESSION_KEY, $viewed);
    }

    private function rememberRecentlyViewed(Product $product): void
    {
        $ids = session()->get(self::RECENT_SESSION_KEY, []);

        // Ürün listede zaten varsa çıkarılır; tekrar başa eklenerek en
        // güncel görüntülenme öne alınır.
        $ids = array_values(array_diff($ids, [$product->id]));
        array_unshift($ids, $product->id);

        session()->put(self::RECENT_SESSION_KEY, array_slice($ids, 0, self::RECENT_LIMIT));
    }

    /**
     * Bu ziyaretçinin son görüntülediği ürünler, en yeniden eskiye.
     *
     * @return \Illuminate\Support\Collection<int, Product>
     */
    public static function recentlyViewed(?int $excludeProductId = null, int $limit = 8): \Illuminate\Support\Collection
    {
        $ids = session()->get(self::RECENT_SESSION_KEY, []);

        if ($excludeProductId !== null) {
            $ids = array_values(array_diff($ids, [$excludeProductId]));
        }

        $ids = array_slice($ids, 0, $limit);

        if (empty($ids)) {
            return collect();
        }

        // whereIn sırayı korumaz; ürünleri session'daki sıraya göre diziyoruz
        // — en son bakılan en başta çıksın diye.
        $products = Product::with('category')->whereIn('id', $ids)->get()->keyBy('id');

        return collect($ids)
            ->map(fn ($id) => $products->get($id))
            ->filter()
            ->values();
    }

    /**
     * Son N güne ait günlük görüntülenme sayıları.
     *
     * @return array<string, int>  'Y-m-d' => adet
     */
    public static function dailyCounts(int $days = 30): array
    {
        $rows = ProductView::query()
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->groupBy('day')
            ->pluck('total', 'day')
            ->all();

        $counts = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $counts[$day] = (int) ($rows[$day] ?? 0);
        }

        return $counts;
    }
}
