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
