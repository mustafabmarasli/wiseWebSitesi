<?php

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Mail\BankTransferOrderMail;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderFulfiller;
use Illuminate\Support\Facades\Mail;

function havaleUrunu(float $fiyat = 100.00, int $stok = 10): Product
{
    $kategori = Category::create([
        'name' => 'Havale Kategori', 'slug' => 'havale-kategori-' . uniqid(), 'channel' => 'electronics',
    ]);

    return Product::create([
        'category_id' => $kategori->id,
        'name'        => 'Havale Urunu',
        'slug'        => 'havale-urunu-' . uniqid(),
        'description' => 'Aciklama',
        'price'       => $fiyat,
        'stock'       => $stok,
        'rating'      => 5,
    ]);
}

/** Urunu sepete atip havale ile siparis verir. */
function havaleSiparisi(Product $urun, array $overrides = []): Order
{
    test()->post(route('cart.add'), ['product_id' => $urun->id, 'quantity' => 1]);
    test()->post(route('payment.initiate'), odemePayload($overrides));

    return Order::latest('id')->firstOrFail();
}

it('banka bilgisi girilmemisken odeme sayfasi acilmaz', function () {
    // Varsayilan durum: havale acik ama IBAN/unvan bos, kart kapali.
    // Musteriye parayi nereye gonderecegini soyleyemiyorsak siparis alinmamali.
    $urun = havaleUrunu();
    $this->post(route('cart.add'), ['product_id' => $urun->id, 'quantity' => 1]);

    $this->get(route('checkout'))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('error');
});

it('havale ile siparis beklemede kalir ve banka sayfasina yonlendirir', function () {
    havaleAyarla();
    Mail::fake();

    $urun    = havaleUrunu();
    $siparis = havaleSiparisi($urun);

    expect($siparis->status)->toBe(OrderStatus::Pending->value);
    expect($siparis->payment_type)->toBe('bank_transfer');
    expect($siparis->payment_method)->toBe('Havale / EFT');

    $this->get(route('payment.bank-transfer', $siparis->id))->assertOk();
});

it('havale siparisinde stok odeme onaylanana kadar dusmez', function () {
    havaleAyarla();
    Mail::fake();

    $urun = havaleUrunu(stok: 10);
    havaleSiparisi($urun);

    // Para gelmeden stok dusurmek, odemeyi hic yapmayan siparisler yuzunden
    // gercekte satilabilir urunu rafta yokmus gibi gosterirdi.
    expect($urun->refresh()->stock)->toBe(10);
});

it('yonetici onayinca stok duser ve siparis odendi olur', function () {
    havaleAyarla();
    Mail::fake();

    $urun    = havaleUrunu(stok: 10);
    $siparis = havaleSiparisi($urun);

    (new OrderFulfiller())->markPaid($siparis);

    expect($siparis->refresh()->status)->toBe(OrderStatus::Paid->value);
    expect($siparis->payment_confirmed_at)->not->toBeNull();
    expect($urun->refresh()->stock)->toBe(9);
});

it('odeme onayi iki kez islenmez', function () {
    havaleAyarla();
    Mail::fake();

    $urun    = havaleUrunu(stok: 10);
    $siparis = havaleSiparisi($urun);

    $fulfiller = new OrderFulfiller();

    expect($fulfiller->markPaid($siparis))->toBeTrue();
    expect($fulfiller->markPaid($siparis->refresh()))->toBeFalse();

    // Iki kez cagrilsa da stok yalnizca bir kez dusmeli
    expect($urun->refresh()->stock)->toBe(9);
});

it('onay kupon sayacini bir kez artirir', function () {
    havaleAyarla();
    Mail::fake();

    $kupon = Coupon::create(['code' => 'HAVALE10', 'type' => 'fixed', 'value' => 10.00]);

    $urun = havaleUrunu();
    $this->post(route('cart.add'), ['product_id' => $urun->id, 'quantity' => 1]);
    $this->withSession(['coupon' => ['code' => $kupon->code, 'type' => $kupon->type, 'value' => $kupon->value]])
        ->post(route('payment.initiate'), odemePayload());

    $siparis   = Order::latest('id')->firstOrFail();
    $fulfiller = new OrderFulfiller();

    $fulfiller->markPaid($siparis);
    $fulfiller->markPaid($siparis->refresh());

    expect($kupon->refresh()->used_count)->toBe(1);
});

it('havale indirimi siparis tutarina yansir', function () {
    havaleAyarla(indirimYuzdesi: 10);
    Mail::fake();

    $urun    = havaleUrunu(fiyat: 200.00);
    $siparis = havaleSiparisi($urun);

    expect((float) $siparis->bank_transfer_discount)->toEqual(20.00);
    expect((float) $siparis->total_amount)->toEqual(180.00);
});

it('havale indirimi kupon indiriminden SONRAKI tutar uzerinden hesaplanir', function () {
    havaleAyarla(indirimYuzdesi: 10);
    Mail::fake();

    $kupon = Coupon::create(['code' => 'ELLI', 'type' => 'fixed', 'value' => 50.00]);
    $urun  = havaleUrunu(fiyat: 200.00);

    $this->post(route('cart.add'), ['product_id' => $urun->id, 'quantity' => 1]);
    $this->withSession(['coupon' => ['code' => $kupon->code, 'type' => $kupon->type, 'value' => $kupon->value]])
        ->post(route('payment.initiate'), odemePayload());

    $siparis = Order::latest('id')->firstOrFail();

    // 200 - 50 = 150 ara toplam, %10 = 15 havale indirimi, kalan 135
    expect((float) $siparis->discount_amount)->toEqual(50.00);
    expect((float) $siparis->bank_transfer_discount)->toEqual(15.00);
    expect((float) $siparis->total_amount)->toEqual(135.00);
});

it('havale indirimi kargo ucretine uygulanmaz', function () {
    havaleAyarla(indirimYuzdesi: 10);
    Setting::current()->update(['standard_shipping_cost' => 50.00]);
    Mail::fake();

    $urun    = havaleUrunu(fiyat: 100.00);
    $siparis = havaleSiparisi($urun);

    // 100 - 10 (indirim) + 50 (kargo) = 140; kargo indirime girmez
    expect((float) $siparis->bank_transfer_discount)->toEqual(10.00);
    expect((float) $siparis->total_amount)->toEqual(140.00);
});

it('kapali odeme yontemi POST edilerek zorlanamaz', function () {
    havaleAyarla(kartAcik: false);

    $urun = havaleUrunu();
    $this->post(route('cart.add'), ['product_id' => $urun->id, 'quantity' => 1]);

    $this->post(route('payment.initiate'), odemePayload(['payment_type' => 'card']))
        ->assertRedirect(route('checkout'))
        ->assertSessionHas('error');

    expect(Order::count())->toBe(0);
});

it('banka bilgileri ve siparis numarasi sayfada gosterilir', function () {
    $setting = havaleAyarla();
    Mail::fake();

    $urun    = havaleUrunu();
    $siparis = havaleSiparisi($urun);

    $this->get(route('payment.bank-transfer', $siparis->id))
        ->assertOk()
        ->assertSee($setting->bank_iban)
        ->assertSee($setting->bank_account_holder)
        ->assertSee('Sipariş No: ' . $siparis->id, escape: false);
});

it('baskasinin havale sayfasi goruntulenemez', function () {
    havaleAyarla();
    Mail::fake();

    $urun    = havaleUrunu();
    $siparis = havaleSiparisi($urun);

    // Oturum izni olmayan yeni bir ziyaretci
    $this->flushSession();

    $this->get(route('payment.bank-transfer', $siparis->id))->assertForbidden();
});

it('musteriye banka bilgilerini iceren e-posta gonderilir', function () {
    havaleAyarla();
    Mail::fake();

    $urun    = havaleUrunu();
    $siparis = havaleSiparisi($urun);

    Mail::assertSent(BankTransferOrderMail::class, fn ($mail) => $mail->order->id === $siparis->id);
});

it('odeme onaylandiktan sonra havale sayfasi basari sayfasina yonlenir', function () {
    havaleAyarla();
    Mail::fake();

    $urun    = havaleUrunu();
    $siparis = havaleSiparisi($urun);

    (new OrderFulfiller())->markPaid($siparis);

    $this->get(route('payment.bank-transfer', $siparis->id))
        ->assertRedirectContains(route('payment.success', $siparis->id));
});

it('kart kapaliyken odeme sayfasinda cok yakinda yazar', function () {
    havaleAyarla(kartAcik: false);

    $urun = havaleUrunu();
    $this->post(route('cart.add'), ['product_id' => $urun->id, 'quantity' => 1]);

    $this->get(route('checkout'))
        ->assertOk()
        ->assertSee('Çok Yakında')
        ->assertSee('Havale / EFT')
        ->assertSee('sonraki adımda banka bilgileri gösterilecektir', escape: false);
});

it('yonetici panelden odemeyi onaylayabilir', function () {
    havaleAyarla();
    Mail::fake();

    $urun    = havaleUrunu(stok: 10);
    $siparis = havaleSiparisi($urun);

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ViewOrder::class, ['record' => $siparis->id])
        ->callAction('confirmPayment');

    expect($siparis->refresh()->status)->toBe(OrderStatus::Paid->value);
    expect($urun->refresh()->stock)->toBe(9);
});

it('odenmis sipariste onay dugmesi gorunmez', function () {
    havaleAyarla();
    Mail::fake();

    $urun    = havaleUrunu();
    $siparis = havaleSiparisi($urun);

    (new OrderFulfiller())->markPaid($siparis);

    // Dugme acik kalsaydi ikinci tikta stok tekrar dusulmese bile
    // yoneticiye islem yapilabilirmis gibi gorunurdu.
    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ViewOrder::class, ['record' => $siparis->id])
        ->assertActionHidden('confirmPayment');
});

it('kart siparisinde onay dugmesi gorunmez', function () {
    havaleAyarla(kartAcik: true);

    // Kart siparisi iyzico'ya ulasamadigi icin akis uzerinden uretilemez;
    // onay dugmesinin yalnizca havaleye ait oldugu dogrudan sinanir.
    $siparis = Order::create([
        'first_name'   => 'Test', 'last_name' => 'Kullanici',
        'email'        => 'test@example.com', 'phone' => '05551112233',
        'address'      => 'Adres', 'city' => 'Istanbul',
        'total_amount' => 100.00, 'currency' => 'TRY',
        'status'       => OrderStatus::Pending->value,
        'payment_type' => 'card',
    ]);

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ViewOrder::class, ['record' => $siparis->id])
        ->assertActionHidden('confirmPayment');
});
