<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;

function duyuruAyarla(bool $acik, ?string $metin = 'Cok yakinda satista.', ?string $baslik = 'Yakinda'): void
{
    Setting::current()->update([
        'announcement_enabled' => $acik,
        'announcement_title'   => $baslik,
        'announcement_text'    => $metin,
    ]);
}

function duyuruUrunu(string $channel = 'electronics'): Product
{
    $category = Category::create([
        'name' => 'Kategori', 'slug' => 'kategori-' . uniqid(), 'channel' => $channel,
    ]);

    return Product::create([
        'category_id' => $category->id,
        'name' => 'Urun', 'slug' => 'urun-' . uniqid(),
        'description' => 'Aciklama', 'price' => 100, 'stock' => 5, 'rating' => 5,
    ]);
}

it('duyuru kapaliyken gosterilmez', function () {
    duyuruAyarla(false);
    duyuruUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertDontSee('Cok yakinda satista.');
});

it('duyuru acikken elektronik sayfasinda gosterilir', function () {
    duyuruAyarla(true);
    duyuruUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('Cok yakinda satista.')
        ->assertSee('Yakinda');
});

it('duyuru saglik sayfasinda da gosterilir', function () {
    duyuruAyarla(true);
    duyuruUrunu('health');

    $this->get(route('health.home'))
        ->assertOk()
        ->assertSee('Cok yakinda satista.');
});

it('duyuru ana portal sayfasinda gosterilmez', function () {
    duyuruAyarla(true);

    $this->get(route('landing'))
        ->assertOk()
        ->assertDontSee('Cok yakinda satista.');
});

it('metin bos ise acik olsa bile gosterilmez', function () {
    duyuruAyarla(true, metin: null);
    duyuruUrunu();

    expect(Setting::current()->showsAnnouncement())->toBeFalse();

    $this->get(route('electronics.home'))->assertOk();
});

it('duyuru kapatma butonu ve baslik erisilebilir', function () {
    duyuruAyarla(true);
    duyuruUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('Duyuruyu kapat')      // aria-label
        ->assertSee('aria-modal="true"', escape: false);
});
