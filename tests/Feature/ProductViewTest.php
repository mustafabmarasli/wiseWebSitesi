<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;

function viewableProduct(): Product
{
    $category = Category::create([
        'name'    => 'Kategori',
        'slug'    => 'kategori-' . uniqid(),
        'channel' => 'electronics',
    ]);

    return Product::create([
        'category_id' => $category->id,
        'name'        => 'Test Urun',
        'slug'        => 'test-urun-' . uniqid(),
        'description' => 'Aciklama',
        'price'       => 100.00,
        'stock'       => 10,
        'rating'      => 5,
    ]);
}

it('urun detayi acilinca goruntulenme kaydedilir', function () {
    $product = viewableProduct();

    $this->get(route('product.detail', $product->slug))->assertOk();

    expect(ProductView::count())->toBe(1);
    expect($product->fresh()->view_count)->toBe(1);
});

it('ayni oturumda sayfa yenilemesi tekrar saymaz', function () {
    $product = viewableProduct();

    $this->get(route('product.detail', $product->slug));
    $this->get(route('product.detail', $product->slug));
    $this->get(route('product.detail', $product->slug));

    expect($product->fresh()->view_count)->toBe(1);
});

it('tekilleme suresi gecince tekrar sayilir', function () {
    $product = viewableProduct();

    $this->get(route('product.detail', $product->slug));

    $this->travel(31)->minutes();

    $this->get(route('product.detail', $product->slug));

    expect($product->fresh()->view_count)->toBe(2);
});

it('farkli urunler ayri ayri sayilir', function () {
    $a = viewableProduct();
    $b = viewableProduct();

    $this->get(route('product.detail', $a->slug));
    $this->get(route('product.detail', $b->slug));

    expect($a->fresh()->view_count)->toBe(1);
    expect($b->fresh()->view_count)->toBe(1);
    expect(ProductView::count())->toBe(2);
});

it('giris yapmis kullanicinin goruntulemesi kullaniciya baglanir', function () {
    $user    = User::factory()->create();
    $product = viewableProduct();

    $this->actingAs($user)->get(route('product.detail', $product->slug));

    expect(ProductView::sole()->user_id)->toBe($user->id);
});

it('ziyaretci kimligi ham IP olarak saklanmaz', function () {
    $product = viewableProduct();

    $this->get(route('product.detail', $product->slug));

    $hash = ProductView::sole()->visitor_hash;

    expect($hash)->not->toContain('127.0.0.1');
    expect(strlen($hash))->toBe(64);
});

it('donusum orani hesaplanir', function () {
    $product = viewableProduct();
    $product->update(['view_count' => 200, 'satis_sayisi' => 10]);

    expect($product->fresh()->conversion_rate)->toEqual(5.0);
});

it('hic goruntulenmemis urunun donusum orani null', function () {
    expect(viewableProduct()->conversion_rate)->toBeNull();
});

it('gunluk goruntulenme sayilari 30 gunluk seri dondurur', function () {
    $counts = \App\Services\ProductViewRecorder::dailyCounts(30);

    expect($counts)->toHaveCount(30);
    expect(array_key_last($counts))->toBe(now()->format('Y-m-d'));
});

it('urun silinince goruntulenme kayitlari da silinir', function () {
    $product = viewableProduct();
    $this->get(route('product.detail', $product->slug));

    expect(ProductView::count())->toBe(1);

    $product->delete();

    expect(ProductView::count())->toBe(0);
});
