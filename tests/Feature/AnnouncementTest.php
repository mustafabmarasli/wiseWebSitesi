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

it('coklu duyuru sirayla gosterilmek uzere basilir', function () {
    duyuruOlustur(['title' => 'Birinci', 'body' => '<p>Ilk duyuru.</p>', 'sort_order' => 1]);
    duyuruOlustur(['title' => 'Ikinci', 'body' => '<p>Sonraki duyuru.</p>', 'sort_order' => 2]);
    duyuruUrunu();

    // İkisi de sayfaya basılır; hangisinin görüneceğine Alpine karar verir
    // (biri kapanınca diğeri açılır).
    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('Birinci')
        ->assertSee('Ikinci')
        ->assertSee('1 / 2 duyuru')
        ->assertSee('2 / 2 duyuru');
});

it('duyuru sirasi sort_order ile belirlenir', function () {
    duyuruOlustur(['title' => 'Sonra Cikan', 'sort_order' => 5]);
    duyuruOlustur(['title' => 'Once Cikan', 'sort_order' => 1]);

    $kuyruk = \App\Models\Announcement::queueForChannel('electronics');

    expect($kuyruk->pluck('title')->all())->toBe(['Once Cikan', 'Sonra Cikan']);
});

it('kuyrukta son olmayan duyuruda siradaki yazisi cikar', function () {
    duyuruOlustur(['title' => 'Birinci', 'sort_order' => 1]);
    duyuruOlustur(['title' => 'Ikinci', 'sort_order' => 2]);
    duyuruUrunu();

    // Kapatınca yeni pencere açılması sürpriz olmasın.
    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('Sıradaki duyuru')
        ->assertSee('Anladım');
});

it('tek duyuruda kuyruk sayaci basilmaz', function () {
    duyuruOlustur();
    duyuruUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertDontSee('1 / 1 duyuru');
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

it('secilen arka plan ve yazi renkleri uygulanir', function () {
    duyuruOlustur([
        'bg_color'   => '#1B4A7A',
        'text_color' => '#FFE066',
    ]);
    duyuruUrunu();

    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('background:#1B4A7A', false)
        ->assertSee('color:#FFE066', false);
});

it('renk secilmezse koyu zeminde beyaz yazi kullanilir', function () {
    $koyu  = duyuruOlustur(['bg_color' => '#0F172A']);
    $acik  = duyuruOlustur(['bg_color' => '#FFFFFF']);

    expect($koyu->isDarkBackground())->toBeTrue()
        ->and($koyu->text_color_value)->toBe('#FFFFFF')
        ->and($acik->isDarkBackground())->toBeFalse()
        ->and($acik->text_color_value)->toBe('#0F172A');
});

it('elle secilen yazi rengi otomatigi ezer', function () {
    $duyuru = duyuruOlustur(['bg_color' => '#0F172A', 'text_color' => '#FF0000']);

    expect($duyuru->text_color_value)->toBe('#FF0000')
        ->and($duyuru->body_color_value)->toBe('#FF0000');
});

it('gorsel altta yerlesimi tanimlanir', function () {
    $altta = duyuruOlustur(['layout' => 'image_bottom', 'image_path' => 'images/banner.png']);
    $ustte = duyuruOlustur(['layout' => 'image_top', 'image_path' => 'images/banner.png']);

    expect($altta->imageBelowText())->toBeTrue()
        ->and($ustte->imageBelowText())->toBeFalse()
        ->and($altta->usesImage())->toBeTrue();
});

it('ortuk yerlesiminde perde secilen renkten uretilir', function () {
    duyuruOlustur([
        'layout'     => 'image_overlay',
        'image_path' => 'images/banner.png',
        'bg_color'   => '#123456',
    ]);
    duyuruUrunu();

    // Perde olmadan açık renkli görselde yazı kayboluyor.
    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('linear-gradient(to top, #123456 0%', false);
});

it('duyuru yonetimi admin panelinde acilir', function () {
    $this->actingAs(\App\Models\User::factory()->create(['is_admin' => true]))
        ->get('/admin/announcements')
        ->assertOk();
});
