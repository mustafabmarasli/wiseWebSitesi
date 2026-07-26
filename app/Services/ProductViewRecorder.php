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
     * Ürün görüntülenmesini kaydeder.
     *
     * Sayfa yenilemeleri ve ileri/geri gezinmeleri saymamak için oturum bazlı
     * tekilleştirme yapılır; aksi halde rakamlar gerçeği yansıtmaz.
     */
    public function record(Product $product, Request $request): void
    {
        if ($this->recentlyViewed($product)) {
            return;
        }

        $this->remember($product);

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

    private function recentlyViewed(Product $product): bool
    {
        $viewed = session()->get(self::SESSION_KEY, []);
        $seenAt = $viewed[$product->id] ?? null;

        return $seenAt !== null && $seenAt > now()->subMinutes(self::DEDUPE_MINUTES)->timestamp;
    }

    private function remember(Product $product): void
    {
        $viewed = session()->get(self::SESSION_KEY, []);
        $cutoff = now()->subMinutes(self::DEDUPE_MINUTES)->timestamp;

        // Süresi geçmiş kayıtları at, oturum sonsuza kadar büyümesin
        $viewed = array_filter($viewed, fn ($ts) => $ts > $cutoff);

        $viewed[$product->id] = now()->timestamp;

        session()->put(self::SESSION_KEY, $viewed);
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
