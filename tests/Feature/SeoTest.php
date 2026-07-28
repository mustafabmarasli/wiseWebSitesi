<?php

use App\Models\Category;
use App\Models\Product;

function seoUrun(string $channel = 'electronics'): Product
{
    $category = Category::create([
        'name' => 'Gelistirme Kartlari', 'slug' => 'gelistirme-kartlari-' . uniqid(), 'channel' => $channel,
    ]);

    return Product::create([
        'category_id' => $category->id,
        'name'        => 'ESP32-S3 Super Mini',
        'slug'        => 'esp32-s3-super-mini-' . uniqid(),
        'description' => 'WiFi ve Bluetooth destekli kompakt gelistirme karti.',
        'price'       => 499.00,
        'stock'       => 12,
        'rating'      => 4.8,
    ]);
}

/** Sayfadaki JSON-LD bloklarini cozumler. */
function jsonLd(string $html): array
{
    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m);

    return array_values(array_filter(array_map(
        fn ($ham) => json_decode(trim($ham), true),
        $m[1]
    )));
}

it('sitemap.xml urun ve kategorileri icerir', function () {
    $urun = seoUrun();

    $res = $this->get('/sitemap.xml')->assertOk();

    expect($res->headers->get('content-type'))->toContain('application/xml');

    $xml = $res->getContent();

    expect($xml)->toContain('<urlset');
    expect($xml)->toContain(route('product.detail', $urun->slug));
    expect($xml)->toContain(route('category', $urun->category->slug));
    expect($xml)->toContain(route('landing'));
});

it('danismanlik kapaliyken sitemapte yer almaz', function () {
    \App\Models\Setting::current()->update(['consulting_enabled' => false]);

    expect($this->get('/sitemap.xml')->getContent())->not->toContain('/danismanlik');
});

it('danismanlik acikken sitemapte yer alir', function () {
    \App\Models\Setting::current()->update(['consulting_enabled' => true]);

    expect($this->get('/sitemap.xml')->getContent())->toContain('/danismanlik');
});

it('sitemapteki her adres gercekten acilir', function () {
    // Canli sitede yasal sayfalarin 6'si 404 veriyordu: sitemap'e URL slug'i
    // yerine gorunum dosyasi adi (alt cizgili) yazilmisti.
    seoUrun();

    $xml = $this->get('/sitemap.xml')->getContent();
    preg_match_all('|<loc>([^<]+)</loc>|', $xml, $m);

    expect($m[1])->not->toBeEmpty();

    $bozuk = [];
    foreach ($m[1] as $url) {
        $yol = parse_url($url, PHP_URL_PATH) ?: '/';

        if ($this->get($yol)->getStatusCode() !== 200) {
            $bozuk[] = $yol;
        }
    }

    expect($bozuk)->toBeEmpty('sitemapte acilmayan adres var: ' . implode(', ', $bozuk));
});

it('sitemap gecerli xml uretir', function () {
    seoUrun();

    $xml = $this->get('/sitemap.xml')->getContent();

    libxml_use_internal_errors(true);
    $dom = simplexml_load_string($xml);

    expect($dom)->not->toBeFalse('sitemap gecersiz XML uretti');
});

it('robots.txt sepet ve hesap sayfalarini kapatir', function () {
    $robots = file_get_contents(public_path('robots.txt'));

    expect($robots)->toContain('Disallow: /sepet');
    expect($robots)->toContain('Disallow: /hesabim');
    expect($robots)->toContain('Disallow: /admin');
    expect($robots)->toContain('Sitemap:');
});

it('urun sayfasinda Product semasi vardir', function () {
    $urun = seoUrun();

    $html = $this->get(route('product.detail', $urun->slug))->assertOk()->getContent();
    $semalar = collect(jsonLd($html));

    $product = $semalar->firstWhere('@type', 'Product');

    expect($product)->not->toBeNull();
    expect($product['name'])->toBe('ESP32-S3 Super Mini');
    expect($product['offers']['price'])->toBe('499.00');
    expect($product['offers']['priceCurrency'])->toBe('TRY');
    expect($product['offers']['availability'])->toBe('https://schema.org/InStock');
});

it('stok bitince sema OutOfStock der', function () {
    $urun = seoUrun();
    $urun->update(['stock' => 0]);

    $html = $this->get(route('product.detail', $urun->slug))->getContent();
    $product = collect(jsonLd($html))->firstWhere('@type', 'Product');

    expect($product['offers']['availability'])->toBe('https://schema.org/OutOfStock');
});

it('semada aggregateRating BULUNMAMALI', function () {
    // Puanlar gercek musteri yorumu degil; uydurma degerlendirme
    // Google politikasi ihlalidir ve tum siteyi rich result'tan dusurur.
    $urun = seoUrun();

    $html = $this->get(route('product.detail', $urun->slug))->getContent();
    $product = collect(jsonLd($html))->firstWhere('@type', 'Product');

    expect($product)->not->toHaveKey('aggregateRating');
    expect($product)->not->toHaveKey('review');
});

it('urun sayfasinda BreadcrumbList vardir', function () {
    $urun = seoUrun();

    $html = $this->get(route('product.detail', $urun->slug))->getContent();
    $kirinti = collect(jsonLd($html))->firstWhere('@type', 'BreadcrumbList');

    expect($kirinti)->not->toBeNull();
    expect($kirinti['itemListElement'])->toHaveCount(3);
});

it('her sayfada Organization semasi vardir', function () {
    seoUrun();

    $html = $this->get(route('electronics.home'))->getContent();

    expect(collect(jsonLd($html))->firstWhere('@type', 'Organization'))->not->toBeNull();
});

it('urun sayfasinda Open Graph etiketleri vardir', function () {
    $urun = seoUrun();

    $this->get(route('product.detail', $urun->slug))
        ->assertOk()
        ->assertSee('property="og:type" content="product"', escape: false)
        ->assertSee('property="og:title" content="ESP32-S3 Super Mini"', escape: false)
        ->assertSee('name="twitter:card" content="summary_large_image"', escape: false);
});

it('kanal sayfalarinda tek h1 vardir', function () {
    seoUrun();

    foreach ([route('electronics.home'), route('health.home')] as $url) {
        $html = $this->get($url)->getContent();
        expect(substr_count($html, '<h1'))->toBe(1, "birden fazla h1: {$url}");
    }
});

it('elektronik ve saglik meta aciklamalari farklidir', function () {
    seoUrun();

    $el = $this->get(route('electronics.home'))->getContent();
    $sa = $this->get(route('health.home'))->getContent();

    preg_match('/<meta name="description" content="([^"]*)"/', $el, $m1);
    preg_match('/<meta name="description" content="([^"]*)"/', $sa, $m2);

    expect($m1[1])->not->toBe($m2[1]);
    expect($m1[1])->not->toContain('lens');       // elektronik lensten bahsetmemeli
    expect($m2[1])->not->toContain('ESP32');      // saglik karttan bahsetmemeli
});

it('cdn scriptlerinde SRI vardir', function () {
    seoUrun();

    // Portal sayfasi bagimsiz tasarim, bu scriptleri yuklemiyor;
    // magaza sayfalari layouts.app uzerinden yukluyor.
    $html = $this->get(route('electronics.home'))->getContent();

    // Surum SABIT olmali; @11 gibi aralik kullanilirsa CDN yeni yama
    // yayinladiginda hash tutmaz ve script hic yuklenmez.
    expect($html)->toContain('sweetalert2@11.26.25');
    expect($html)->toContain('alpinejs@3.15.12');
    expect(substr_count($html, 'integrity="sha384-'))->toBeGreaterThanOrEqual(2);
});

it('portal sayfasinda tek h1 ve canonical vardir', function () {
    $html = $this->get(route('landing'))->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1);
    expect($html)->toContain('rel="canonical"');
    expect($html)->toContain('property="og:title"');
});

it('portal sayfasinda WebSite semasi vardir', function () {
    $html = $this->get(route('landing'))->getContent();

    expect(collect(jsonLd($html))->firstWhere('@type', 'WebSite'))->not->toBeNull();
});
