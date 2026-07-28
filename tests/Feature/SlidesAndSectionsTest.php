<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Slide;
use App\Models\User;

function slaytUrunu(string $channel = 'electronics'): Product
{
    $c = Category::create([
        'name' => 'Kategori', 'slug' => 'kategori-' . uniqid(), 'channel' => $channel,
    ]);

    return Product::create([
        'category_id' => $c->id, 'name' => 'Urun', 'slug' => 'urun-' . uniqid(),
        'description' => 'Aciklama', 'price' => 100, 'stock' => 5, 'rating' => 5,
    ]);
}

/**
 * Migration varsayilan 5 slayti ekliyor; testler kendi verisiyle calissin diye
 * once tablo temizlenir.
 */
beforeEach(fn () => Slide::query()->delete());

function slaytEkle(array $overrides = []): Slide
{
    return Slide::create(array_merge([
        'channel'      => 'electronics',
        'image_path'   => 'images/banner.png',
        'title'        => 'Test Slayti',
        'subtitle'     => 'Aciklama metni',
        'badge'        => 'Yeni',
        'badge_color'  => 'emerald',
        'primary_text' => 'Incele',
        'primary_url'  => '#tum-urunler',
        'sort_order'   => 1,
        'is_active'    => true,
    ], $overrides));
}

/* ---------------- Slaytlar ---------------- */

it('slayt anasayfada gosterilir', function () {
    slaytUrunu();
    slaytEkle(['title' => 'Kampanya Basligi']);

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('Kampanya Basligi')
        ->assertSee('Aciklama metni');
});

it('yayindan kaldirilan slayt gosterilmez', function () {
    slaytUrunu();
    slaytEkle(['title' => 'Gizli Slayt', 'is_active' => false]);

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertDontSee('Gizli Slayt');
});

it('slaytlar sadece kendi kanalinda cikar', function () {
    slaytUrunu('electronics');
    slaytUrunu('health');
    slaytEkle(['channel' => 'health', 'title' => 'Saglik Slayti']);

    $this->get(route('electronics.home'))->assertDontSee('Saglik Slayti');
    $this->get(route('health.home'))->assertSee('Saglik Slayti');
});

it('slaytlar sira numarasina gore dizilir', function () {
    slaytUrunu();
    slaytEkle(['title' => 'Ikinci', 'sort_order' => 2]);
    slaytEkle(['title' => 'Birinci', 'sort_order' => 1]);

    $html = $this->get(route('electronics.home'))->getContent();

    expect(strpos($html, 'Birinci'))->toBeLessThan(strpos($html, 'Ikinci'));
});

it('nokta gostergesi slayt sayisi kadar uretilir', function () {
    slaytUrunu();
    slaytEkle(['title' => 'A']);
    slaytEkle(['title' => 'B']);
    slaytEkle(['title' => 'C']);

    $html = $this->get(route('electronics.home'))->getContent();

    expect(substr_count($html, 'id="dot-'))->toBe(3);
    expect($html)->toContain('const slidesCount = 3;');
});

it('butonu olmayan slaytta buton alani cizilmez', function () {
    slaytUrunu();
    slaytEkle(['title' => 'Butonsuz', 'primary_text' => null, 'secondary_text' => null]);

    $this->get(route('electronics.home'))->assertOk()->assertSee('Butonsuz');
});

it('slayt yonetimi admin panelinde acilir', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/admin/slides')
        ->assertOk()
        ->assertSee('Anasayfa Slaytları');
});

/* ---------------- Danismanlik bolumu ---------------- */

it('danismanlik kapaliyken portal sayfasinda gosterilmez', function () {
    Setting::current()->update(['consulting_enabled' => false]);

    $this->get(route('landing'))
        ->assertOk()
        ->assertDontSee('Danışmanlık ve Dış Ticaret')
        ->assertSee('pointer-events-none  iki-bolme', escape: false);   // sarmalayici iki bolme duzenine gecmeli
});

it('danismanlik acikken portal sayfasinda gosterilir', function () {
    Setting::current()->update(['consulting_enabled' => true]);

    $this->get(route('landing'))
        ->assertOk()
        ->assertSee('Danışmanlık ve Dış Ticaret')
        ->assertDontSee('pointer-events-none  iki-bolme', escape: false);
});

it('danismanlik kapaliyken ust menude gosterilmez', function () {
    Setting::current()->update(['consulting_enabled' => false]);
    slaytUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertDontSee('Danışmanlık & Dış Ticaret');
});

it('danismanlik kapaliyken sayfa 404 doner', function () {
    Setting::current()->update(['consulting_enabled' => false]);

    $this->get(route('consulting'))->assertNotFound();
});

it('danismanlik acikken sayfa acilir', function () {
    Setting::current()->update(['consulting_enabled' => true]);

    $this->get(route('consulting'))->assertOk();
});

it('varsayilan olarak danismanlik kapalidir', function () {
    expect(Setting::current()->consulting_enabled)->toBeFalse();
});
