<?php

use App\Models\Category;
use App\Models\Product;

function searchableProduct(string $name, string $channel = 'electronics'): Product
{
    $category = Category::create([
        'name'    => 'Kategori',
        'slug'    => 'kategori-' . uniqid(),
        'channel' => $channel,
    ]);

    return Product::create([
        'category_id' => $category->id,
        'name'        => $name,
        'slug'        => Str::slug($name) . '-' . uniqid(),
        'description' => 'Aciklama',
        'price'       => 100.00,
        'stock'       => 5,
        'rating'      => 5,
    ]);
}

it('arama sayfasi acilir', function () {
    searchableProduct('ESP32 Kart');

    $this->get(route('product.search', ['q' => 'ESP32']))->assertOk();
});

it('saglik kanalinda arama sayfasi acilir', function () {
    searchableProduct('Lens Kutusu', 'health');

    $this->get(route('product.search', ['q' => 'Lens', 'channel' => 'health']))->assertOk();
});

it('arama terimi olmadan da acilir', function () {
    searchableProduct('ESP32 Kart');

    $this->get(route('product.search'))->assertOk();
});

it('eslesen urunu listeler', function () {
    searchableProduct('ESP32 DevKit');

    $this->get(route('product.search', ['q' => 'DevKit']))
        ->assertOk()
        ->assertSee('ESP32 DevKit');
});

it('arama basligi cift escape edilmez', function () {
    searchableProduct('ESP32 Kart');

    // Tirnak iceren terim ekranda &amp;quot; olarak degil, duzgun gorunmeli
    $this->get(route('product.search', ['q' => 'ESP32']))
        ->assertOk()
        ->assertDontSee('&amp;quot;', escape: false);
});

it('kategori sayfasinda kategoriye ozel arama kutusu vardir', function () {
    $product = searchableProduct('ESP32 DevKit');
    $kategori = $product->category;

    $this->get(route('category', $kategori->slug))
        ->assertOk()
        ->assertSee('id="category-search-input"', false)
        ->assertSee($kategori->name . ' içinde ara');
});

it('kategori icinde arama yalnizca o kategoriyi tarar', function () {
    $kat1 = Category::create(['name' => 'Birinci', 'slug' => 'birinci-' . uniqid(), 'channel' => 'electronics']);
    $kat2 = Category::create(['name' => 'Ikinci', 'slug' => 'ikinci-' . uniqid(), 'channel' => 'electronics']);

    $urun1 = Product::create([
        'category_id' => $kat1->id, 'name' => 'Ortak Kelime Bir',
        'slug' => 'ortak-bir-' . uniqid(), 'description' => 'Aciklama', 'price' => 100, 'stock' => 5, 'rating' => 5,
    ]);
    Product::create([
        'category_id' => $kat2->id, 'name' => 'Ortak Kelime Iki',
        'slug' => 'ortak-iki-' . uniqid(), 'description' => 'Aciklama', 'price' => 100, 'stock' => 5, 'rating' => 5,
    ]);

    $this->get(route('category', $kat1->slug) . '?q=Ortak')
        ->assertOk()
        ->assertSee('Ortak Kelime Bir')
        ->assertDontSee('Ortak Kelime Iki');
});

it('kategori icinde arama sonuc sayisini gosterir', function () {
    $product = searchableProduct('ESP32 DevKit');

    $this->get(route('category', $product->category->slug) . '?q=DevKit')
        ->assertOk()
        ->assertSee('için 1 sonuç bulundu');
});

it('kategori icinde eslesmeyen arama urun bulunamadi gosterir', function () {
    $product = searchableProduct('ESP32 DevKit');

    $this->get(route('category', $product->category->slug) . '?q=hicbirsonucyok')
        ->assertOk()
        ->assertSee('Ürün Bulunamadı');
});

it('kategori icindeki arama diger filtrelerle birlikte calisir', function () {
    $kategori = searchableProduct('ESP32 DevKit')->category;
    Product::create([
        'category_id' => $kategori->id, 'name' => 'Pahali ESP32',
        'slug' => 'pahali-esp32-' . uniqid(), 'description' => 'Aciklama', 'price' => 5000, 'stock' => 5, 'rating' => 5,
    ]);

    $this->get(route('category', $kategori->slug) . '?q=ESP32&max_price=200')
        ->assertOk()
        ->assertSee('ESP32 DevKit')
        ->assertDontSee('Pahali ESP32');
});
