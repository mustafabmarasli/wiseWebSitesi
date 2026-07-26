<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;

function testProduct(array $overrides = []): Product
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
        'stock'       => 50,
        'rating'      => 5,
    ], $overrides));
}

function cartSession(Product $product, int $qty = 1): array
{
    return [
        'cart' => [
            $product->id => [
                'name'       => $product->name,
                'quantity'   => $qty,
                'price'      => $product->price,
                'image_path' => $product->image_path,
                'slug'       => $product->slug,
            ],
        ],
    ];
}

function checkoutPayload(array $overrides = []): array
{
    return array_merge([
        'first_name'      => 'Test',
        'last_name'       => 'Kullanici',
        'email'           => 'misafir@example.com',
        'phone'           => '05551112233',
        'address'         => 'Test Mahallesi No:1',
        'city'            => 'Istanbul',
        'identity_number' => '11111111111',
        'agree_sales'     => '1',
        'agree_kvkk'      => '1',
        'agree_accuracy'  => '1',
    ], $overrides);
}

it('sepette gorunen indirim odeme adiminda da gecerlidir', function () {
    $product = testProduct(['price' => 200.00]);

    $coupon = Coupon::create([
        'code'  => 'INDIRIM50',
        'type'  => 'fixed',
        'value' => 50.00,
    ]);

    // Misafir bu kuponu daha once ayni e-posta ile kullanmis
    Order::create([
        'user_id'      => null,
        'first_name'   => 'Test',
        'last_name'    => 'Kullanici',
        'email'        => 'misafir@example.com',
        'phone'        => '05551112233',
        'address'      => 'Adres',
        'city'         => 'Istanbul',
        'total_amount' => 150.00,
        'currency'     => 'TRY',
        'status'       => 'paid',
        'coupon_code'  => $coupon->code,
    ]);

    $session = cartSession($product) + [
        'coupon' => ['code' => $coupon->code, 'type' => $coupon->type, 'value' => $coupon->value],
    ];

    // Odeme sayfasi indirimi gosteriyor mu?
    $checkoutShowsDiscount = $this->withSession($session)
        ->get(route('checkout'))
        ->viewData('discount') > 0;

    // Odeme baslatildiginda ne oluyor?
    $response = $this->withSession($session)->post(route('payment.initiate'), checkoutPayload());

    if ($checkoutShowsDiscount) {
        // Indirim vaat edildiyse tam fiyattan siparis OLUSMAMALI
        $order = Order::where('status', 'pending')->latest('id')->first();

        expect($order?->total_amount)->not->toEqual('200.00',
            'Odeme sayfasi indirim gosterdi ama siparis tam fiyattan olusturuldu.');
    }
})->skip(fn () => ! extension_loaded('curl'), 'iyzico cagrisi icin curl gerekli');

it('gecersiz kupon sessizce dusurulmez, kullanici uyarilir', function () {
    $product = testProduct(['price' => 200.00]);

    $coupon = Coupon::create([
        'code'       => 'SURESIGECMIS',
        'type'       => 'fixed',
        'value'      => 50.00,
        'expires_at' => now()->subDay(),
    ]);

    $session = cartSession($product) + [
        'coupon' => ['code' => $coupon->code, 'type' => $coupon->type, 'value' => $coupon->value],
    ];

    $this->withSession($session)
        ->post(route('payment.initiate'), checkoutPayload())
        ->assertRedirect(route('checkout'))
        ->assertSessionHas('error');

    expect(Order::count())->toBe(0);
});

it('indirim sepet tutarini asarsa odeme baslatilmaz', function () {
    $product = testProduct(['price' => 30.00]);

    $coupon = Coupon::create([
        'code'  => 'BUYUKINDIRIM',
        'type'  => 'fixed',
        'value' => 500.00,
    ]);

    $session = cartSession($product) + [
        'coupon' => ['code' => $coupon->code, 'type' => $coupon->type, 'value' => $coupon->value],
    ];

    $this->withSession($session)
        ->post(route('payment.initiate'), checkoutPayload())
        ->assertRedirect(route('checkout'))
        ->assertSessionHas('error');

    expect(Order::count())->toBe(0);
});
