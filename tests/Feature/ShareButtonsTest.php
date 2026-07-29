<?php

use App\Models\Category;
use App\Models\Product;

function shareProduct(): Product
{
    $category = Category::create([
        'name'    => 'Kategori',
        'slug'    => 'kategori-' . uniqid(),
        'channel' => 'electronics',
    ]);

    return Product::create([
        'category_id' => $category->id,
        'name'        => 'Paylasilacak Urun',
        'slug'        => 'paylasilacak-urun-' . uniqid(),
        'description' => 'Aciklama',
        'price'       => 100.00,
        'stock'       => 10,
        'rating'      => 5,
    ]);
}

it('urun sayfasinda whatsapp paylasim baglantisi vardir', function () {
    $product = shareProduct();

    $this->get(route('product.detail', $product->slug))
        ->assertOk()
        ->assertSee('wa.me', false)
        ->assertSee('Bağlantıyı Kopyala');
});

it('paylasim baglantilari utm etiketi tasir', function () {
    $product = shareProduct();

    $html = $this->get(route('product.detail', $product->slug))->assertOk()->getContent();

    // Analytics'te kanalı ancak bu etiketle ayırt edebiliyoruz.
    foreach (['whatsapp', 'x', 'facebook'] as $kanal) {
        expect($html)->toContain(urlencode('utm_source=' . $kanal . '&utm_medium=share'));
    }
});

it('paylasilan adres urunun kendi sayfasidir', function () {
    $product = shareProduct();

    $this->get(route('product.detail', $product->slug))
        ->assertOk()
        ->assertSee('data-url="' . route('product.detail', $product->slug) . '"', false);
});

it('paylasim kartlari icin open graph etiketleri basilir', function () {
    $product = shareProduct();

    // Bunlar olmadan WhatsApp'ta bağlantı çıplak metin görünür.
    $this->get(route('product.detail', $product->slug))
        ->assertOk()
        ->assertSee('property="og:title" content="' . $product->name . '"', false)
        ->assertSee('property="og:type" content="product"', false)
        ->assertSee('name="twitter:card" content="summary_large_image"', false);
});
