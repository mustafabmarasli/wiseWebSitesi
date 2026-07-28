<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * sitemap.xml — ürün, kategori ve statik sayfalardan üretilir.
 *
 * Harici paket kullanılmıyor: içerik hacmi küçük, ihtiyaç basit ve
 * sunucuda `composer install` gerektirmemesi dağıtımı kolaylaştırıyor.
 */
class SitemapController extends Controller
{
    private const CACHE_MINUTES = 60;

    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addMinutes(self::CACHE_MINUTES), fn () => $this->build());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function build(): string
    {
        $urls = [];

        // Giriş sayfaları
        $urls[] = $this->url(route('landing'), '1.0', 'daily');
        $urls[] = $this->url(route('electronics.home'), '0.9', 'daily');
        $urls[] = $this->url(route('health.home'), '0.9', 'daily');
        // Danışmanlık bölümü panelden kapatılabiliyor; kapalıyken sayfa 404
        // döndüğü için site haritasına konmamalı.
        if (\App\Models\Setting::current()->consulting_enabled) {
            $urls[] = $this->url(route('consulting'), '0.6', 'monthly');
        }

        $urls[] = $this->url(route('contact'), '0.5', 'monthly');

        // Kategoriler
        foreach (Category::select('slug', 'updated_at')->get() as $category) {
            $urls[] = $this->url(
                route('category', $category->slug),
                '0.8',
                'weekly',
                $category->updated_at
            );
        }

        // Ürünler
        Product::select('slug', 'updated_at')->orderBy('id')->chunk(500, function ($products) use (&$urls) {
            foreach ($products as $product) {
                $urls[] = $this->url(
                    route('product.detail', $product->slug),
                    '0.7',
                    'weekly',
                    $product->updated_at
                );
            }
        });

        // Yasal sayfalar — nadiren değişir, düşük öncelik.
        // DİKKAT: Buradaki değerler URL slug'larıdır (TİRE ile), görünüm dosyası
        // adları değil (onlar alt çizgi kullanıyor). PageController::procedural()
        // içindeki beyaz listeyle birebir aynı olmalı.
        $urls[] = $this->url(route('kvkk'), '0.3', 'yearly');

        foreach ([
            'mesafeli-satis',
            'on-bilgilendirme',
            'gizlilik-guvenlik',
            'cerez-politikasi',
            'teslimat-iade',
            'kullanim-kosullari',
        ] as $sayfa) {
            $urls[] = $this->url(route('procedural', $sayfa), '0.3', 'yearly');
        }

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . implode("\n", $urls) . "\n"
            . '</urlset>';
    }

    private function url(string $loc, string $priority, string $changefreq, $lastmod = null): string
    {
        $satir = '  <url>' . "\n"
            . '    <loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>' . "\n";

        if ($lastmod) {
            $satir .= '    <lastmod>' . $lastmod->toAtomString() . '</lastmod>' . "\n";
        }

        return $satir
            . '    <changefreq>' . $changefreq . '</changefreq>' . "\n"
            . '    <priority>' . $priority . '</priority>' . "\n"
            . '  </url>';
    }
}
