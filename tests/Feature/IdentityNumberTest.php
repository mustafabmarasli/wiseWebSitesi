<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

function tcUrunu(float $fiyat): Product
{
    $kategori = Category::create([
        'name' => 'TC Kategori', 'slug' => 'tc-kategori-' . uniqid(), 'channel' => 'electronics',
    ]);

    return Product::create([
        'category_id' => $kategori->id,
        'name'        => 'TC Urunu',
        'slug'        => 'tc-urunu-' . uniqid(),
        'description' => 'Aciklama',
        'price'       => $fiyat,
        'stock'       => 50,
        'rating'      => 5,
    ]);
}

/** Sepete ekleyip TC'siz siparis dener. */
function tcsizSiparis(Product $urun, array $overrides = [])
{
    test()->post(route('cart.add'), ['product_id' => $urun->id, 'quantity' => 1]);

    return test()->post(route('payment.initiate'), odemePayload(array_merge(
        ['identity_number' => ''],
        $overrides
    )));
}

beforeEach(function () {
    havaleAyarla();
    Setting::current()->update(['identity_required_threshold' => 12000]);
    Mail::fake();
});

it('haddin altindaki siparis TC olmadan tamamlanir', function () {
    // Vergi mukellefi olmayan nihai tuketiciye kesilen faturada TC zorunlu
    // degil; gerekmiyorken toplamak KVKK veri minimizasyonuna aykiri.
    tcsizSiparis(tcUrunu(500.00));

    $siparis = Order::latest('id')->first();

    expect($siparis)->not->toBeNull();
    expect($siparis->identity_number)->toBeNull();
});

it('haddi asan sipariste TC zorunludur', function () {
    tcsizSiparis(tcUrunu(15000.00))
        ->assertSessionHasErrors('identity_number');

    expect(Order::count())->toBe(0);
});

it('ticari faturada tutar ne olursa olsun TC zorunludur', function () {
    tcsizSiparis(tcUrunu(500.00), [
        'is_corporate' => '1',
        'company_name' => 'Ornek Ltd.',
        'tax_number'   => '1234567890',
        'tax_office'   => 'Kadikoy',
    ])->assertSessionHasErrors('identity_number');

    expect(Order::count())->toBe(0);
});

it('kartla odemede TC zorunludur', function () {
    // iyzico API'si buyer.identityNumber alanini zorunlu tutuyor.
    havaleAyarla(kartAcik: true);

    tcsizSiparis(tcUrunu(500.00), ['payment_type' => 'card'])
        ->assertSessionHasErrors('identity_number');
});

it('esik sifir yapilirsa her sipariste TC zorunlu olur', function () {
    Setting::current()->update(['identity_required_threshold' => 0]);

    tcsizSiparis(tcUrunu(100.00))->assertSessionHasErrors('identity_number');
});

it('zorunlu olmasa da girilen TC gecersizse reddedilir', function () {
    // Bos birakilabilmesi, uydurma bir numaranin kaydedilebilecegi
    // anlamina gelmemeli.
    tcsizSiparis(tcUrunu(500.00), ['identity_number' => '11111111111'])
        ->assertSessionHasErrors('identity_number');
});

it('zorunlu olmasa da girilen gecerli TC kaydedilir', function () {
    tcsizSiparis(tcUrunu(500.00), ['identity_number' => GECERLI_TC]);

    expect(Order::latest('id')->first()->identity_number)->toBe(GECERLI_TC);
});

it('havale indirimi tutari haddin altina cekerse TC istenmez', function () {
    // 12.500 TL siparis, %10 havale indirimi -> 11.250 TL, esik 12.000
    havaleAyarla(indirimYuzdesi: 10);
    Setting::current()->update(['identity_required_threshold' => 12000]);

    tcsizSiparis(tcUrunu(12500.00));

    expect(Order::count())->toBe(1);
    expect((float) Order::latest('id')->first()->total_amount)->toEqual(11250.00);
});

it('odeme sayfasi haddin altinda TC yi istege bagli gosterir', function () {
    $urun = tcUrunu(500.00);
    $this->post(route('cart.add'), ['product_id' => $urun->id, 'quantity' => 1]);

    $this->get(route('checkout'))
        ->assertOk()
        ->assertSee('boş bırakabilirsiniz', escape: false);
});

it('odeme sayfasi haddi asinca TC yi zorunlu gosterir', function () {
    $urun = tcUrunu(15000.00);
    $this->post(route('cart.add'), ['product_id' => $urun->id, 'quantity' => 1]);

    $this->get(route('checkout'))
        ->assertOk()
        ->assertSee('bu sipariş için zorunludur', escape: false);
});
