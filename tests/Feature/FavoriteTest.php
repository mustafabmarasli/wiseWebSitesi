<?php

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;

function favProduct(): Product
{
    $category = Category::create([
        'name'    => 'Kategori',
        'slug'    => 'kategori-' . uniqid(),
        'channel' => 'electronics',
    ]);

    return Product::create([
        'category_id' => $category->id,
        'name'        => 'Favori Urun',
        'slug'        => 'favori-urun-' . uniqid(),
        'description' => 'Aciklama',
        'price'       => 100.00,
        'stock'       => 10,
        'rating'      => 5,
    ]);
}

it('favori dugmesi urun basliginin yaninda ve yazili', function () {
    $product = favProduct();

    $this->actingAs(User::factory()->create())
        ->get(route('product.detail', $product->slug))
        ->assertOk()
        // Yalnız simge ne demek olduğunu anlatmıyor; yazı da olmalı.
        ->assertSee('Favorilerime Ekle')
        ->assertSee('id="fav-btn"', false);
});

it('favorideki urunde dugme dolu kalp ve farkli yazi gosterir', function () {
    $product = favProduct();
    $user = User::factory()->create();

    Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $html = $this->actingAs($user)->get(route('product.detail', $product->slug))
        ->assertOk()
        ->assertSee('Favorilerimde')
        ->getContent();

    // İçi dolu kalp: fill="currentColor". Boşken fill="none".
    expect($html)->toContain('id="fav-icon" class="h-5 w-5 shrink-0" fill="currentColor"');
});

it('misafire favori yerine giris baglantisi gosterilir', function () {
    $product = favProduct();

    $this->get(route('product.detail', $product->slug))
        ->assertOk()
        ->assertSee(route('login'), false)
        ->assertDontSee('id="fav-btn"', false);
});

it('ajax istegi json doner ve sayfa yenilenmez', function () {
    $product = favProduct();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('favorite.toggle'), ['product_id' => $product->id])
        ->assertOk()
        ->assertJson(['success' => true, 'status' => 'added']);

    expect($user->favoriteProducts()->count())->toBe(1);
});

it('ikinci ajax istegi favoriden cikarir', function () {
    $product = favProduct();
    $user = User::factory()->create();

    Favorite::create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user)
        ->postJson(route('favorite.toggle'), ['product_id' => $product->id])
        ->assertOk()
        ->assertJson(['status' => 'removed']);

    expect($user->favoriteProducts()->count())->toBe(0);
});

it('misafir favori ekleyemez', function () {
    $product = favProduct();

    $this->post(route('favorite.toggle'), ['product_id' => $product->id])
        ->assertRedirect(route('login'));

    expect(Favorite::count())->toBe(0);
});

it('favoriye eklenen urun favoriler sayfasinda listelenir', function () {
    $product = favProduct();
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('favorite.toggle'), ['product_id' => $product->id]);

    $this->actingAs($user)->get(route('profile.favorites'))
        ->assertOk()
        ->assertSee($product->name);
});
