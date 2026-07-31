<?php

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductViewRecorder;

function recentProduct(string $name): Product
{
    $category = Category::create([
        'name' => 'Kategori', 'slug' => 'kategori-' . uniqid(), 'channel' => 'electronics',
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

it('hic gezinti yokken bos liste doner', function () {
    expect(ProductViewRecorder::recentlyViewed())->toBeEmpty();
});

it('urun sayfasi acilinca son goruntulenenler listesine eklenir', function () {
    $a = recentProduct('Urun A');
    $b = recentProduct('Urun B');

    $this->get(route('product.detail', $a->slug));

    // B'nin sayfasında A görünmeli — kendisi hariç, gezinti geçmişi.
    $this->get(route('product.detail', $b->slug))
        ->assertOk()
        ->assertSee('Son Görüntülenen Ürünler')
        ->assertSee('Urun A');
});

it('su an bakilan urun kendi rafinda cikmaz', function () {
    $a = recentProduct('Tekrar Bakilan Urun');

    $this->get(route('product.detail', $a->slug));

    // Aynı ürüne tekrar bakınca kendi "son görüntülenenler" rafında
    // görünmemeli.
    $this->get(route('product.detail', $a->slug))
        ->assertOk()
        ->assertDontSee('Son Görüntülenen Ürünler');
});

it('ilk ziyarette raf hic basilmaz', function () {
    $a = recentProduct('Ilk Ziyaret Urunu');

    $this->get(route('product.detail', $a->slug))
        ->assertOk()
        ->assertDontSee('Son Görüntülenen Ürünler');
});

it('en son bakilan urun basa gecer', function () {
    $a = recentProduct('Once Bakilan');
    $b = recentProduct('Sonra Bakilan');
    $c = recentProduct('Ucuncu Urun');

    $this->get(route('product.detail', $a->slug));
    $this->get(route('product.detail', $b->slug));

    $html = $this->get(route('product.detail', $c->slug))->assertOk()->getContent();

    // "Sonra Bakilan" (en son) "Once Bakilan"dan ÖNCE çıkmalı.
    expect(strpos($html, 'Sonra Bakilan'))->toBeLessThan(strpos($html, 'Once Bakilan'));
});

it('ayni urune tekrar bakinca listenin basina alinir', function () {
    $a = recentProduct('Eski Sirali Urun');
    $b = recentProduct('Yeni Sirali Urun');
    $d = recentProduct('Goruntulenen Sayfa');

    $this->get(route('product.detail', $a->slug));
    $this->get(route('product.detail', $b->slug));
    $this->get(route('product.detail', $a->slug)); // A'ya tekrar bakıldı, başa geçmeli

    $html = $this->get(route('product.detail', $d->slug))->assertOk()->getContent();

    expect(strpos($html, 'Eski Sirali Urun'))->toBeLessThan(strpos($html, 'Yeni Sirali Urun'));
});

it('raf en fazla on iki urun tutar', function () {
    $urunler = collect(range(1, 13))->map(fn ($i) => recentProduct('Sira Urunu ' . $i));

    foreach ($urunler as $urun) {
        $this->get(route('product.detail', $urun->slug));
    }

    // 13. ürünü görüntüledikten sonra kendisi hariç tutulduğu için liste
    // en fazla 12 kayıt taşır; en eski (1.) düşmüş olmalı.
    $liste = ProductViewRecorder::recentlyViewed(excludeProductId: null, limit: 20);

    expect($liste)->toHaveCount(12)
        ->and($liste->pluck('name'))->not->toContain('Sira Urunu 1')
        ->and($liste->pluck('name'))->toContain('Sira Urunu 13');
});

it('limit parametresi sonucu sinirlar', function () {
    $urunler = collect(range(1, 5))->map(fn ($i) => recentProduct('Limit Urunu ' . $i));
    $son = recentProduct('Son Sayfa');

    foreach ($urunler as $urun) {
        $this->get(route('product.detail', $urun->slug));
    }
    $this->get(route('product.detail', $son->slug));

    expect(ProductViewRecorder::recentlyViewed(excludeProductId: $son->id, limit: 2))->toHaveCount(2);
});
