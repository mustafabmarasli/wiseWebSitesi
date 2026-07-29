<?php

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

function adresUrunu(): Product
{
    $category = Category::create([
        'name' => 'Kategori', 'slug' => 'kategori-' . uniqid(), 'channel' => 'electronics',
    ]);

    return Product::create([
        'category_id' => $category->id,
        'name' => 'Urun', 'slug' => 'urun-' . uniqid(),
        'description' => 'Aciklama', 'price' => 100, 'stock' => 10, 'rating' => 5,
    ]);
}

function sepetleGir(Product $p): void
{
    test()->post(route('cart.add'), ['product_id' => $p->id, 'quantity' => 1]);
}

// Odeme yontemi yapilandirilmamissa /odeme sepete geri yonlendirir.
beforeEach(fn () => havaleAyarla());

it('kayitli adres yoksa secim bolumu gosterilmez', function () {
    $urun = adresUrunu();
    sepetleGir($urun);

    $this->actingAs(User::factory()->create())
        ->get(route('checkout'))
        ->assertOk()
        ->assertDontSee('Kayıtlı Adreslerim');
});

it('misafirde secim bolumu gosterilmez', function () {
    $urun = adresUrunu();
    sepetleGir($urun);

    $this->get(route('checkout'))
        ->assertOk()
        ->assertDontSee('Kayıtlı Adreslerim');
});

it('kayitli adres konum adlariyla listelenir', function () {
    $konum = testLocation();
    $user  = User::factory()->create();

    Address::create([
        'user_id'    => $user->id,
        'title'      => 'Ev Adresim',
        'first_name' => 'Ahmet', 'last_name' => 'Yilmaz',
        'phone'      => '05551112233',
        'address'    => 'Test Sokak No:1',
        'city'       => 'Kayseri',
        'zip_code'   => '38000',
    ] + $konum);

    $urun = adresUrunu();
    sepetleGir($urun);

    $this->actingAs($user)
        ->get(route('checkout'))
        ->assertOk()
        ->assertSee('Kayıtlı Adreslerim')
        ->assertSee('Ev Adresim')
        ->assertSee('Ahmet')
        ->assertSee('Numune Evler')   // mahalle
        ->assertSee('Melikgazi')      // ilce
        ->assertSee('Kayseri');       // il
});

it('kayitli adres verisi konum id lerini icerir', function () {
    $konum = testLocation();
    $user  = User::factory()->create();

    Address::create([
        'user_id' => $user->id, 'title' => 'Ev',
        'first_name' => 'A', 'last_name' => 'B', 'phone' => '05551112233',
        'address' => 'Adres', 'city' => 'Kayseri',
    ] + $konum);

    $urun = adresUrunu();
    sepetleGir($urun);

    $adresler = $this->actingAs($user)->get(route('checkout'))->viewData('savedAddresses');

    expect($adresler)->toHaveCount(1);
    expect($adresler[0]['province_id'])->toBe($konum['province_id']);
    expect($adresler[0]['district_id'])->toBe($konum['district_id']);
    expect($adresler[0]['neighborhood_id'])->toBe($konum['neighborhood_id']);
    expect($adresler[0]['address_detail'])->toBe('Adres');
});

it('misafir uye olunca adresine konum id leri kopyalanir', function () {
    $konum = testLocation();

    $order = Order::create([
        'user_id' => null,
        'first_name' => 'Ahmet', 'last_name' => 'Yilmaz',
        'email' => 'misafir@example.com', 'phone' => '05551112233',
        'address' => 'Test Sokak No:1', 'city' => 'Kayseri', 'zip_code' => '38000',
        'total_amount' => 100, 'currency' => 'TRY', 'status' => 'paid',
    ] + $konum);

    $this->withSession(['order_access' => [$order->id]])
        ->post(route('payment.register-guest', $order->id), [
            'password' => 'yenisifre123', 'password_confirmation' => 'yenisifre123',
        ])
        ->assertRedirect(route('profile.index'));

    $adres = Address::sole();

    expect($adres->province_id)->toBe($konum['province_id']);
    expect($adres->district_id)->toBe($konum['district_id']);
    expect($adres->neighborhood_id)->toBe($konum['neighborhood_id']);
});
