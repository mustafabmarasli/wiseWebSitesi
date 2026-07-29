<?php

use App\Models\Category;
use App\Models\Product;

function bolumUrunu(string $ad, array $overrides = []): Product
{
    $kategori = Category::firstOrCreate(
        ['slug' => 'bolum-test'],
        ['name' => 'Bolum Test', 'channel' => 'electronics'],
    );

    return Product::create(array_merge([
        'category_id'  => $kategori->id,
        'name'         => $ad,
        'slug'         => Str::slug($ad),
        'description'  => 'Aciklama',
        'price'        => 100.00,
        'stock'        => 10,
        'rating'       => 5,
        'satis_sayisi' => 0,
    ], $overrides));
}

it('ayni urun birden fazla bolumde tekrar etmez', function () {
    // Hem indirimli hem cok satan bir urun: eskiden iki bolumde birden cikardi
    bolumUrunu('Hem Indirimli Hem Populer', ['eski_fiyat' => 200, 'price' => 100, 'satis_sayisi' => 999]);
    bolumUrunu('Sadece Populer', ['satis_sayisi' => 500]);
    bolumUrunu('Sadece Yeni');

    $res = $this->get(route('electronics.home'))->assertOk();

    // HTML'de saymak yaniltir: urun karti adi alt/title/metin olmak uzere
    // uc kez barindirir, ayrica Vitrin bolumu bilerek ayni urunu one cikarir.
    // Asil garanti, uc listenin birbiriyle kesismemesidir.
    $listeler = collect(['discountedProducts', 'popularProducts', 'newProducts'])
        ->map(fn ($ad) => $res->viewData($ad)->pluck('id')->all());

    $tumIdler = $listeler->flatten()->all();

    expect($tumIdler)->not->toBeEmpty();
    expect(count($tumIdler))->toBe(count(array_unique($tumIdler)), 'bir urun birden fazla bolumde gorunuyor');
});

it('tum urunler bolumu kaldirildi', function () {
    bolumUrunu('Test Urunu');

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertDontSee('>Tüm Ürünler<', escape: false);
});

it('tum urunlere giden buton vardir', function () {
    bolumUrunu('Test Urunu');

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('Tüm Ürünleri Gör');
});

it('bolum sirasi indirimli populer yeni seklindedir', function () {
    bolumUrunu('Indirimli Urun', ['eski_fiyat' => 200, 'price' => 100]);
    bolumUrunu('Populer Urun', ['satis_sayisi' => 500]);
    bolumUrunu('Yeni Urun');

    $html = $this->get(route('electronics.home'))->getContent();

    $ind  = strpos($html, 'İndirimli Ürünler');
    $pop  = strpos($html, 'Popüler Ürünler');
    $yeni = strpos($html, 'Yeni Eklenenler');

    expect($ind)->toBeLessThan($pop);
    expect($pop)->toBeLessThan($yeni);
});

it('urun yoksa bolumler cizilmez', function () {
    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertDontSee('İndirimli Ürünler')
        ->assertDontSee('Yeni Eklenenler');
});
