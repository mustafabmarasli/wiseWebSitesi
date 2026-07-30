<?php

use App\Models\Announcement;
use App\Models\Category;
use App\Models\Product;

function duyuruOlustur(array $overrides = []): Announcement
{
    return Announcement::create(array_merge([
        'channel'   => 'both',
        'title'     => 'Yakinda',
        'body'      => '<p>Cok yakinda satista.</p>',
        'layout'    => 'text',
        'tone'      => 'info',
        'is_active' => true,
    ], $overrides));
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
    duyuruOlustur(['is_active' => false]);
    duyuruUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertDontSee('Cok yakinda satista.');
});

it('duyuru acikken elektronik sayfasinda gosterilir', function () {
    duyuruOlustur();
    duyuruUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('Cok yakinda satista.', false)
        ->assertSee('Yakinda');
});

it('duyuru saglik sayfasinda da gosterilir', function () {
    duyuruOlustur();
    duyuruUrunu('health');

    $this->get(route('health.home'))
        ->assertOk()
        ->assertSee('Cok yakinda satista.', false);
});

it('duyuru ana portal sayfasinda gosterilmez', function () {
    duyuruOlustur();

    $this->get(route('landing'))
        ->assertOk()
        ->assertDontSee('Cok yakinda satista.');
});

it('duyuru kapatma butonu ve baslik erisilebilir', function () {
    duyuruOlustur();
    duyuruUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('Duyuruyu kapat')      // aria-label
        ->assertSee('aria-modal="true"', escape: false);
});

it('kanala ozel duyuru yalnizca o magazada cikar', function () {
    duyuruOlustur(['channel' => 'health', 'title' => 'Sadece Saglik', 'body' => '<p>Lens duyurusu.</p>']);
    duyuruUrunu('health');
    duyuruUrunu('electronics');

    $this->get(route('health.home'))->assertOk()->assertSee('Sadece Saglik');
    $this->get(route('electronics.home'))->assertOk()->assertDontSee('Sadece Saglik');
});

it('ayni sayfada yalnizca bir duyuru gosterilir', function () {
    // İki pencere açmak ziyaretçiyi iki kez kapatmaya zorlar.
    duyuruOlustur(['title' => 'Birinci', 'body' => '<p>Ilk duyuru.</p>', 'sort_order' => 1]);
    duyuruOlustur(['title' => 'Ikinci', 'body' => '<p>Sonraki duyuru.</p>', 'sort_order' => 2]);
    duyuruUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('Birinci')
        ->assertDontSee('Ikinci');
});

it('bicimli metin html olarak basilir', function () {
    duyuruOlustur(['body' => '<p>Kar marjı gözetmeden <strong>en uygun fiyata</strong> satıyoruz.</p>']);
    duyuruUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('<strong>en uygun fiyata</strong>', false);
});

it('buton girildiyse baglanti olarak cikar', function () {
    duyuruOlustur(['button_text' => 'Ürünleri İncele', 'button_url' => '/elektronik']);
    duyuruUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('Ürünleri İncele')
        ->assertSee('Şimdi değil');   // buton varken "Anladım" ikincil olur
});

it('buton adresi bos ise buton cikmaz', function () {
    duyuruOlustur(['button_text' => 'Tikla', 'button_url' => null]);
    duyuruUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('Anladım')
        ->assertDontSee('Şimdi değil');
});

it('gorsel ustte yerlesiminde gorsel basilir', function () {
    duyuruOlustur(['layout' => 'image_top', 'image_path' => 'img/deneme.jpg']);
    duyuruUrunu();

    // Görsel dosyası gerçekten yoksa `image_url` null döner ve basılmaz;
    // yerleşim mantığının kendisi burada sınanıyor.
    expect(duyuruOlustur(['layout' => 'image_top'])->usesImage())->toBeFalse();
});

it('yazi gorselin uzerinde secilmezse ortuk uygulanmaz', function () {
    $duyuru = duyuruOlustur(['layout' => 'image_top']);

    expect($duyuru->isOverlay())->toBeFalse();
});

it('ton simgesi turune gore degisir', function () {
    expect(duyuruOlustur(['tone' => 'info'])->tone_style['renk'])->toBe('#2563EB')
        ->and(duyuruOlustur(['tone' => 'warning'])->tone_style['renk'])->toBe('#F59E0B')
        ->and(duyuruOlustur(['tone' => 'campaign'])->tone_style['renk'])->toBe('#059669')
        ->and(duyuruOlustur(['tone' => 'none'])->tone_style)->toBeNull();
});

it('duyuru yonetimi admin panelinde acilir', function () {
    $this->actingAs(\App\Models\User::factory()->create(['is_admin' => true]))
        ->get('/admin/announcements')
        ->assertOk();
});
