<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

function feedKategori(array $overrides = []): Category
{
    return Category::create(array_merge([
        'name' => 'Gelistirme Kartlari', 'slug' => 'kart-' . uniqid(), 'channel' => 'electronics',
    ], $overrides));
}

function feedUrun(array $overrides = [], ?Category $kategori = null): Product
{
    Storage::disk('public')->put('products/feed.webp', 'sahte');

    return Product::create(array_merge([
        'category_id' => ($kategori ?? feedKategori())->id,
        'name'        => 'ESP32-S3 Super Mini',
        'slug'        => 'esp32-s3-' . uniqid(),
        'description' => 'WiFi ve Bluetooth destekli kompakt kart.',
        'price'       => 499.00,
        'stock'       => 12,
        'rating'      => 4.8,
        'image_path'  => 'products/feed.webp',
    ], $overrides));
}

/** Akistaki ilk <item> blogunu etiket => deger dizisine cevirir. */
function feedItem(string $xml): array
{
    $sx = simplexml_load_string($xml);
    $item = $sx->channel->item[0] ?? null;

    if (!$item) {
        return [];
    }

    $sonuc = [];
    foreach ($item->children() as $k => $v) {
        $sonuc[$k] = (string) $v;
    }
    foreach ($item->children('g', true) as $k => $v) {
        $sonuc['g:' . $k] = (string) $v;
    }

    return $sonuc;
}

beforeEach(fn () => Storage::fake('public'));

it('akis gecerli xml uretir', function () {
    feedUrun();

    $res = $this->get('/merchant-feed.xml')->assertOk();

    expect($res->headers->get('content-type'))->toContain('application/xml');

    libxml_use_internal_errors(true);
    expect(simplexml_load_string($res->getContent()))->not->toBeFalse('akis gecersiz XML');
});

it('zorunlu Google alanlarini icerir', function () {
    $urun = feedUrun();

    $item = feedItem($this->get('/merchant-feed.xml')->getContent());

    expect($item['g:id'])->toBe((string) $urun->id);
    expect($item['title'])->toBe('ESP32-S3 Super Mini');
    expect($item['link'])->toBe(route('product.detail', $urun->slug));
    expect($item['g:price'])->toBe('499.00 TRY');
    expect($item['g:availability'])->toBe('in_stock');
    expect($item['g:condition'])->toBe('new');
    expect($item['g:image_link'])->not->toBeEmpty();
});

it('stok bitince out_of_stock der', function () {
    feedUrun(['stock' => 0]);

    expect(feedItem($this->get('/merchant-feed.xml')->getContent())['g:availability'])
        ->toBe('out_of_stock');
});

it('marka yoksa identifier_exists no yazar', function () {
    feedUrun(['brand' => null, 'gtin' => null]);

    $item = feedItem($this->get('/merchant-feed.xml')->getContent());

    // Google, tanimlayicisi olmayan urunlerde bunu ister; yoksa urunu reddeder.
    expect($item['g:identifier_exists'])->toBe('no');
    expect($item)->not->toHaveKey('g:brand');
});

it('marka varsa brand ve mpn yazar', function () {
    $urun = feedUrun(['brand' => 'Espressif']);

    $item = feedItem($this->get('/merchant-feed.xml')->getContent());

    expect($item['g:brand'])->toBe('Espressif');
    expect($item['g:mpn'])->toBe((string) $urun->id);
    expect($item)->not->toHaveKey('g:identifier_exists');
});

it('barkod varsa gtin yazar', function () {
    feedUrun(['gtin' => '8681234567890']);

    expect(feedItem($this->get('/merchant-feed.xml')->getContent())['g:gtin'])
        ->toBe('8681234567890');
});

it('gorselsiz urun akisa girmez', function () {
    feedUrun(['image_path' => null]);

    $xml = $this->get('/merchant-feed.xml')->getContent();

    // Gorselsiz urun Google tarafindan reddedilir; hic gondermemeliyiz.
    expect(substr_count($xml, '<item>'))->toBe(0);
});

it('eski fiyat varsa sale_price kullanir', function () {
    feedUrun(['price' => 499.00, 'eski_fiyat' => 599.00]);

    $item = feedItem($this->get('/merchant-feed.xml')->getContent());

    expect($item['g:price'])->toBe('599.00 TRY');
    expect($item['g:sale_price'])->toBe('499.00 TRY');
});

it('kategori google taksonomisini aktarir', function () {
    $kategori = feedKategori(['google_product_category' => '3853']);
    feedUrun([], $kategori);

    $item = feedItem($this->get('/merchant-feed.xml')->getContent());

    expect($item['g:google_product_category'])->toBe('3853');
    expect($item['g:product_type'])->toBe('Gelistirme Kartlari');
});

it('kargo ucreti panel ayarindan gelir', function () {
    Setting::current()->update(['standard_shipping_cost' => 49.90, 'free_shipping_threshold' => null]);
    feedUrun();

    $xml = $this->get('/merchant-feed.xml')->getContent();

    expect($xml)->toContain('<g:country>TR</g:country>');
    expect($xml)->toContain('49.90 TRY');
});

it('ozel karakterler xml i bozmaz', function () {
    feedUrun(['name' => 'Kart & Modül <test> "tırnak"']);

    $xml = $this->get('/merchant-feed.xml')->getContent();

    libxml_use_internal_errors(true);
    expect(simplexml_load_string($xml))->not->toBeFalse('ozel karakter XML i bozdu');
    expect(feedItem($xml)['title'])->toBe('Kart & Modül <test> "tırnak"');
});
