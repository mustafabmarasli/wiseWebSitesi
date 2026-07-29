<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;

function shipProduct(float $price = 100.00, int $stock = 20): Product
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
        'price'       => $price,
        'stock'       => $stock,
        'rating'      => 5,
    ]);
}

function setShipping(float $cost, ?float $threshold = null): void
{
    Setting::current()->update([
        'standard_shipping_cost'  => $cost,
        'free_shipping_threshold' => $threshold,
    ]);
}

it('kargo ucreti sepet toplamina eklenir', function () {
    setShipping(49.90);
    $product = shipProduct(100.00);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->get(route('cart.index'));

    expect($response->viewData('shippingCost'))->toEqual(49.90);
    expect($response->viewData('netTotal'))->toEqual(149.90);
});

it('esik uzerinde kargo ucretsiz olur', function () {
    setShipping(49.90, threshold: 500.00);
    $product = shipProduct(100.00);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 6]);

    $response = $this->get(route('cart.index'));

    expect($response->viewData('shippingCost'))->toEqual(0.0);
    expect($response->viewData('netTotal'))->toEqual(600.00);
});

it('esik altinda kargo ucreti alinir', function () {
    setShipping(49.90, threshold: 500.00);
    $product = shipProduct(100.00);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 4]);

    $response = $this->get(route('cart.index'));

    expect($response->viewData('shippingCost'))->toEqual(49.90);
    expect($response->viewData('netTotal'))->toEqual(449.90);
});

it('ucretsiz kargoya kalan tutar hesaplanir', function () {
    setShipping(49.90, threshold: 500.00);
    $product = shipProduct(100.00);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 3]);

    expect($this->get(route('cart.index'))->viewData('freeShippingRemaining'))->toEqual(200.00);
});

it('esige indirim sonrasi tutar uzerinden bakilir', function () {
    setShipping(49.90, threshold: 500.00);
    $product = shipProduct(100.00);

    $coupon = \App\Models\Coupon::create(['code' => 'INDIRIM', 'type' => 'fixed', 'value' => 200.00]);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 6]);

    // 600 TL sepet, 200 TL indirim -> 400 TL, esigin altinda kaldi
    $response = $this->withSession(['coupon' => ['code' => $coupon->code, 'type' => 'fixed', 'value' => 200.00]])
        ->get(route('cart.index'));

    expect($response->viewData('shippingCost'))->toEqual(49.90);
    expect($response->viewData('netTotal'))->toEqual(449.90);
});

it('kargo ucreti sifirsa daima ucretsizdir', function () {
    setShipping(0.00, threshold: 500.00);
    $product = shipProduct(10.00);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    $response = $this->get(route('cart.index'));

    expect($response->viewData('shippingCost'))->toEqual(0.0);
    expect($response->viewData('freeShippingRemaining'))->toBeNull();
});

it('kargo ucreti siparise kaydedilir', function () {
    havaleAyarla();
    setShipping(49.90);
    $product = shipProduct(100.00);

    $this->post(route('cart.add'), ['product_id' => $product->id, 'quantity' => 1]);

    // iyzico cagrisi basarisiz olsa da siparis pending olarak olusur
    $this->post(route('payment.initiate'), odemePayload());

    $order = Order::latest('id')->first();

    expect($order)->not->toBeNull();
    expect((float) $order->shipping_cost)->toEqual(49.90);
    expect((float) $order->total_amount)->toEqual(149.90);
});

it('kargo ayarlari sayfasi admin panelinde acilir', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get('/admin/shipping-settings')->assertOk();
});

it('kimlik numarasi veritabaninda sifreli tutulur', function () {
    $order = Order::create([
        'first_name' => 'Test', 'last_name' => 'Kullanici',
        'email' => 'test@example.com', 'phone' => '05551112233',
        'address' => 'Adres', 'city' => 'Istanbul',
        'identity_number' => '12345678901',
        'total_amount' => 100.00, 'currency' => 'TRY', 'status' => 'pending',
    ]);

    $raw = \Illuminate\Support\Facades\DB::table('orders')->where('id', $order->id)->value('identity_number');

    expect($raw)->not->toBe('12345678901');
    expect(strlen($raw))->toBeGreaterThan(50);
    expect($order->fresh()->identity_number)->toBe('12345678901');
});
