<?php

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\OrderExporter;
use Illuminate\Support\Collection;

function adminUser(): User
{
    return User::factory()->create(['is_admin' => true]);
}

function detailProduct(): Product
{
    $category = Category::create([
        'name' => 'Kategori', 'slug' => 'kategori-' . uniqid(), 'channel' => 'electronics',
    ]);

    return Product::create([
        'category_id' => $category->id,
        'name' => 'ESP32 Kart', 'slug' => 'esp32-' . uniqid(),
        'description' => 'Aciklama', 'price' => 250.00, 'stock' => 8, 'rating' => 5,
    ]);
}

function fullOrder(array $overrides = []): Order
{
    $order = Order::create(array_merge([
        'first_name' => 'Ahmet', 'last_name' => 'Yilmaz',
        'email' => 'ahmet@example.com', 'phone' => '05551112233',
        'address' => 'Melikgazi Mah. No:5', 'city' => 'Kayseri', 'zip_code' => '38000',
        // Adres artık il/ilçe/mahalle ilişkilerinden gösteriliyor
        ...testLocation(),
        'identity_number' => '12345678901',
        'billing_address' => 'Fatura Mah. No:9', 'billing_city' => 'Ankara',
        'is_corporate' => true, 'company_name' => 'Ornek Ltd',
        'tax_number' => '1234567890', 'tax_office' => 'Kocasinan',
        'payment_method' => 'iyzico Kredi Kartı',
        'shipping_method' => 'Standart Kargo', 'shipping_cost' => 49.90,
        'coupon_code' => 'INDIRIM10', 'discount_amount' => 25.00,
        'total_amount' => 524.90, 'currency' => 'TRY', 'status' => 'paid',
        'iyzico_payment_id' => 'PAY-123',
    ], $overrides));

    OrderItem::create([
        'order_id' => $order->id, 'product_id' => null,
        'product_name' => 'ESP32 Kart', 'quantity' => 2,
        'unit_price' => 250.00, 'total_price' => 500.00,
    ]);

    return $order->fresh('items');
}

it('siparis detay sayfasi acilir ve tum bilgileri gosterir', function () {
    $order = fullOrder();

    $this->actingAs(adminUser())
        ->get("/admin/orders/{$order->id}")
        ->assertOk()
        ->assertSee('Ahmet')
        ->assertSee('ahmet@example.com')
        ->assertSee('05551112233')
        ->assertSee('Melikgazi Mah. No:5')
        ->assertSee('Kayseri')          // il
        ->assertSee('Melikgazi')        // ilçe
        ->assertSee('Numune Evler')     // mahalle
        ->assertSee('Ornek Ltd')
        ->assertSee('INDIRIM10')
        ->assertSee('ESP32 Kart');
});

it('siparis detayinda tc kimlik cozulmus gosterilir', function () {
    $order = fullOrder();

    $this->actingAs(adminUser())
        ->get("/admin/orders/{$order->id}")
        ->assertOk()
        ->assertSee('12345678901');
});

it('urun detay sayfasi goruntulenme istatistiklerini gosterir', function () {
    $product = detailProduct();
    $product->update(['view_count' => 120, 'satis_sayisi' => 6]);

    $this->actingAs(adminUser())
        ->get("/admin/products/{$product->id}")
        ->assertOk()
        ->assertSee('Görüntülenme İstatistikleri')
        ->assertSee('120')
        ->assertSee('5%'); // 6/120 = %5 donusum
});

it('urun detayinda goruntulenme yoksa bilgi mesaji cikar', function () {
    $product = detailProduct();

    $this->actingAs(adminUser())
        ->get("/admin/products/{$product->id}")
        ->assertOk()
        ->assertSee('Son 14 günde görüntülenme kaydı yok.');
});

it('siparis disa aktarimi tum alanlari icerir', function () {
    $order = fullOrder();

    $response = OrderExporter::download(Collection::make([$order]));

    ob_start();
    $response->sendContent();
    $csv = ob_get_clean();

    expect($csv)->toContain('Sipariş No');
    expect($csv)->toContain('TC Kimlik No');
    expect($csv)->toContain('Kupon Kullanıldı mı');
    expect($csv)->toContain('Ahmet');
    expect($csv)->toContain('ahmet@example.com');
    expect($csv)->toContain('05551112233');
    expect($csv)->toContain('Melikgazi Mah. No:5');
    expect($csv)->toContain('12345678901');
    expect($csv)->toContain('Ornek Ltd');
    expect($csv)->toContain('ESP32 Kart x2');
    expect($csv)->toContain('INDIRIM10');
    expect($csv)->toContain('Kurumsal');
});

it('kupon kullanilmayan siparis hayir olarak isaretlenir', function () {
    $order = fullOrder(['coupon_code' => null, 'discount_amount' => 0]);

    $response = OrderExporter::download(Collection::make([$order]));

    ob_start();
    $response->sendContent();
    $csv = ob_get_clean();

    // "Kupon Kullanıldı mı" sütunu; fputcsv gereksiz yere tırnak koymaz
    expect($csv)->toContain(',Hayır,');
});

it('siparis listesi excel indirme aksiyonlarini gosterir', function () {
    fullOrder();

    $this->actingAs(adminUser())
        ->get('/admin/orders')
        ->assertOk()
        ->assertSee('Tümünü Excel İndir');
});
