<?php

use App\Models\Category;
use App\Models\Product;

function makeProduct(array $overrides = []): Product
{
    $category = Category::create([
        'name'    => 'Test Kategori',
        'slug'    => 'test-kategori-' . uniqid(),
        'channel' => 'electronics',
    ]);

    return Product::create(array_merge([
        'category_id' => $category->id,
        'name'        => 'Test Urun',
        'slug'        => 'test-urun-' . uniqid(),
        'description' => 'Aciklama',
        'price'       => 100.00,
        'stock'       => 10,
        'rating'      => 5,
    ], $overrides));
}

it('negatif adet sepete eklenemez', function () {
    $product = makeProduct();

    $this->from(route('cart.index'))
        ->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => -5])
        ->assertSessionHasErrors('quantity');

    expect(session('cart'))->toBeNull();
});

it('sifir adet sepete eklenemez', function () {
    $product = makeProduct();

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 0])
        ->assertSessionHasErrors('quantity');
});

it('stoktan fazla adet stok seviyesine kirpilir', function () {
    $product = makeProduct(['stock' => 3]);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 99]);

    expect(session('cart')[$product->id]['quantity'])->toBe(3);
});

it('tekrarlanan eklemeler de stok sinirini asamaz', function () {
    $product = makeProduct(['stock' => 4]);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 3]);
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 3]);

    expect(session('cart')[$product->id]['quantity'])->toBe(4);
});

it('olmayan urun sepete eklenemez', function () {
    $this->post(route('cart.add'), ['product_id' => 999999, 'quantity' => 1])
        ->assertSessionHasErrors('product_id');
});

it('sepette olmayan urun guncellenmeye calisilinca sepeti bozmaz', function () {
    $product = makeProduct();
    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    $this->postJson(route('cart.update'), ['id' => 999999, 'quantity' => 5])
        ->assertNotFound();

    // Sepet hala gecerli: sayfa 500 vermemeli
    $this->get(route('cart.index'))->assertOk();
});

it('sepet sayfasi bozuk session verisiyle cokmeze', function () {
    $product = makeProduct();

    // Eski surumden kalmis / kurcalanmis session: sadece quantity var
    $this->withSession(['cart' => [$product->id => ['quantity' => 2]]])
        ->get(route('cart.index'))
        ->assertOk();
});

it('urun fiyati degisince sepet guncel fiyati kullanir', function () {
    $product = makeProduct(['price' => 100.00]);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2]);

    $product->update(['price' => 250.00]);

    $total = $this->get(route('cart.index'))->viewData('total');

    expect($total)->toEqual(500.00);
});

it('stogu tukenen urun sepetten dusurulur', function () {
    $product = makeProduct(['stock' => 5]);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 3]);

    $product->update(['stock' => 0]);

    $cart = $this->get(route('cart.index'))->viewData('cart');

    expect($cart)->toBeEmpty();
});

it('silinen urun sepetten dusurulur', function () {
    $product = makeProduct();

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);
    $product->delete();

    $cart = $this->get(route('cart.index'))->viewData('cart');

    expect($cart)->toBeEmpty();
});

it('stok azalirsa odeme sayfasi kullaniciyi sepete geri gonderir', function () {
    $product = makeProduct(['stock' => 10]);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 8]);

    $product->update(['stock' => 2]);

    $this->get(route('checkout'))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('error');
});

it('update stok ustunde adet kabul etmez', function () {
    $product = makeProduct(['stock' => 3]);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    $this->postJson(route('cart.update'), ['id' => $product->id, 'quantity' => 50])
        ->assertOk()
        ->assertJson(['success' => true, 'quantity' => 3, 'capped' => true]);
});
