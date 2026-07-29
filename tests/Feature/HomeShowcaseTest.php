<?php

use App\Models\Category;
use App\Models\Product;

function showcaseProduct(string $name, int $stock, int $sales): Product
{
    // static kullanılmıyor: RefreshDatabase her testte veritabanını geri alıyor,
    // önbelleğe alınan kategori id'si bir sonraki testte geçersiz kalıyordu.
    $category = Category::firstOrCreate(
        ['slug' => 'elektronik-test'],
        ['name' => 'Elektronik Test', 'channel' => 'electronics'],
    );

    return Product::create([
        'category_id'  => $category->id,
        'name'         => $name,
        'slug'         => Str::slug($name),
        'description'  => 'Aciklama',
        'price'        => 100.00,
        'stock'        => $stock,
        'satis_sayisi' => $sales,
        'rating'       => 5,
    ]);
}

it('vitrin stokta olan urunu tercih eder', function () {
    // En cok satan ama tukenmis
    showcaseProduct('Tukenmis Populer', stock: 0, sales: 500);
    // Daha az satan ama stokta
    $inStock = showcaseProduct('Stokta Olan', stock: 10, sales: 5);

    $response = $this->get(route('electronics.home'))->assertOk();

    // Stoktaki urun vitrinde "+ Sepet" butonuyla cikmali
    $response->assertSee('Stokta Olan');
    expect($inStock->stock)->toBeGreaterThan(0);
});

it('stogu biten vitrin urununde sepet butonu gosterilmez', function () {
    showcaseProduct('Tukenmis Urun', stock: 0, sales: 500);

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('Tükendi · Haber Ver');
});

it('stokta olan vitrin urununde sepet butonu gosterilir', function () {
    showcaseProduct('Stoklu Urun', stock: 25, sales: 500);

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('+ Sepet');
});

it('stogu biten urun sepete eklenemez', function () {
    $product = showcaseProduct('Tukenmis', stock: 0, sales: 10);

    $this->from(route('electronics.home'))
        ->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])
        ->assertRedirect(route('electronics.home'))
        ->assertSessionHas('error');

    expect(session('cart'))->toBeNull();
});

it('stokta olan urun anasayfadan sepete eklenir', function () {
    $product = showcaseProduct('Stoklu', stock: 5, sales: 10);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1])
        ->assertSessionHas('success');

    expect(session('cart')[$product->id]['quantity'])->toBe(1);
});

it('vitrin kartinda gorsele tiklanabilir', function () {
    $product = showcaseProduct('Tiklanabilir Urun', stock: 10, sales: 500);

    $this->get(route('electronics.home'))
        ->assertOk()
        // Görsel bağlantısı: yalnızca vitrin kartında bulunan aria-label.
        ->assertSee('href="' . route('product.detail', $product->slug) . '"', false)
        ->assertSee('aria-label="' . $product->name . ' ürün detayı"', false);
});

it('vitrin kartinda baslik da urune baglanir', function () {
    $product = showcaseProduct('Baslik Baglantisi', stock: 10, sales: 500);

    $html = $this->get(route('electronics.home'))->assertOk()->getContent();

    $url = preg_quote(route('product.detail', $product->slug), '/');

    // Başlık <h3> içindeki metin bağlantı olmalı — kullanıcı ürün adına da tıklar.
    expect($html)->toMatch('/<h3[^>]*>\s*<a href="' . $url . '"/');
});

it('vitrin is_featured olan urunu onceliklendirir', function () {
    // En cok satan ama is_featured olmayan
    $notFeatured = showcaseProduct('Normal Populer', stock: 10, sales: 500);
    // Az satan ama is_featured olan
    $featured = showcaseProduct('Vitrin Urunu', stock: 5, sales: 2);
    $featured->update(['is_featured' => true]);

    $response = $this->get(route('electronics.home'))->assertOk();
    
    // Vitrin Urunu vitrin kartlarinda gorunmeli
    $response->assertSee('Vitrin Urunu');
});
