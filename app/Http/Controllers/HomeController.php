<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Redirect to the main landing page portal.
     */
    public function index()
    {
        return redirect()->route('landing');
    }

    /**
     * Display the Electronics Store.
     */
    public function electronics()
    {
        return $this->kanalSayfasi('electronics', 'Elektronik');
    }

    /**
     * Display the Health/Medical Store.
     */
    public function health()
    {
        return $this->kanalSayfasi('health', 'Sağlık & Lens');
    }

    /**
     * İki kanalın anasayfası aynı kurgudur; yalnızca kanal adı değişir.
     */
    private function kanalSayfasi(string $channel, string $channelTitle)
    {
        $categories = Category::where('channel', $channel)->get();
        $categoryIds = $categories->pluck('id');

        // Anasayfa bölümleri BİRBİRİNİ TEKRAR ETMEZ: her bölüm, kendinden
        // öncekilerde gösterilen ürünleri dışarıda bırakır. Önceden "Tüm Ürünler"
        // her şeyi döküyor, sonraki bölümler aynı ürünleri tekrar gösteriyordu.
        $gosterilen = [];

        // 1. İndirimli ürünler — en dikkat çekici olan, önce gelir
        $discountedProducts = Product::with('category')
            ->whereIn('category_id', $categoryIds)
            ->whereNotNull('eski_fiyat')
            ->whereColumn('eski_fiyat', '>', 'price')
            ->orderByDesc('satis_sayisi')
            ->take(8)
            ->get();

        $gosterilen = array_merge($gosterilen, $discountedProducts->pluck('id')->all());

        // 2. Popüler ürünler — hiç satılmamış ürün "popüler" sayılmaz.
        // Bu filtre olmadan kalan tüm ürünler bu bölüme doluyor ve
        // "Yeni Eklenenler" bölümüne hiçbir şey kalmıyordu.
        $popularProducts = Product::with('category')
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $gosterilen)
            ->where('satis_sayisi', '>', 0)
            ->orderByDesc('satis_sayisi')
            ->take(8)
            ->get();

        $gosterilen = array_merge($gosterilen, $popularProducts->pluck('id')->all());

        // 3. Yeni eklenenler — kalanlardan, sayfaya tazelik katar
        $newProducts = Product::with('category')
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $gosterilen)
            ->latest('id')
            ->take(8)
            ->get();

        // 4. Vitrin — panelden işaretlenenler
        $showcaseProducts = Product::with('category')
            ->whereIn('category_id', $categoryIds)
            ->where('is_featured', true)
            ->orderByDesc('stock')
            ->take(2)
            ->get();

        if ($showcaseProducts->count() < 2) {
            $excludeIds = $showcaseProducts->pluck('id')->toArray();
            $fallback = Product::with('category')
                ->whereIn('category_id', $categoryIds)
                ->whereNotIn('id', $excludeIds)
                ->orderByDesc('satis_sayisi')
                ->get()
                ->sortByDesc(fn ($p) => $p->stock > 0)
                ->take(2 - $showcaseProducts->count());

            $showcaseProducts = $showcaseProducts->concat($fallback);
        }

        $slides = \App\Models\Slide::forChannel($channel);

        // Sağ raftaki rehber yazıları. Kanala özel yazıların yanında "Genel"
        // olanlar da gösterilir — genel rehberler iki kanalı da ilgilendirir.
        $blogPosts = \App\Models\Post::published()
            ->whereIn('channel', [$channel, 'general'])
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        return view('home', compact('categories', 'popularProducts', 'discountedProducts', 'newProducts', 'showcaseProducts', 'channel', 'channelTitle', 'slides', 'blogPosts'));
    }
}
