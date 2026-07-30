<?php

use App\Models\MarketingConsent;
use App\Models\User;

function uyeOl(array $overrides = []): \Illuminate\Testing\TestResponse
{
    return test()->post(route('register'), array_merge([
        'name'                  => 'Yeni Musteri',
        'email'                 => 'musteri@example.com',
        'password'              => 'sifre-1234',
        'password_confirmation' => 'sifre-1234',
        'kvkk_consent'          => '1',
    ], $overrides));
}

it('kvkk onayi tek basina pazarlama onayi sayilmaz', function () {
    // KVKK onayı verinin İŞLENMESİNİ, 6563 onayı pazarlama iletisi
    // GÖNDERİLMESİNİ kapsar. İkisi ayrıdır.
    uyeOl()->assertSessionHas('success');

    expect(MarketingConsent::count())->toBe(0);
});

it('kutu isaretlenirse eposta onayi kaydedilir', function () {
    uyeOl(['eposta_izni' => '1'])->assertSessionHas('success');

    $onay = MarketingConsent::first();

    expect($onay->channel)->toBe('email')
        ->and($onay->email)->toBe('musteri@example.com')
        ->and($onay->status)->toBe('granted')
        ->and($onay->source)->toBe('register')
        ->and($onay->consented_at)->not->toBeNull()
        // İspat yükü göndericide: IP ve tarih olmadan onay savunulamaz.
        ->and($onay->ip_address)->not->toBeNull()
        ->and($onay->unsubscribe_token)->not->toBeNull();
});

it('kayit formunda pazarlama kutusu isaretli gelmez', function () {
    // Önceden işaretli kutu geçerli onay sayılmaz.
    $html = $this->get(route('register'))->assertOk()->getContent();

    expect($html)->toContain('name="eposta_izni"')
        ->and($html)->not->toContain('name="eposta_izni" value="1" id="register-eposta-izni" checked');
});

it('ayni kisi icin ikinci onay satiri acilmaz', function () {
    MarketingConsent::grant('email', email: 'tekrar@example.com', source: 'register');
    MarketingConsent::grant('email', email: 'TEKRAR@example.com', source: 'checkout');

    expect(MarketingConsent::count())->toBe(1)
        ->and(MarketingConsent::first()->source)->toBe('checkout');
});

it('telefon tek bicime indirgenir', function () {
    expect(MarketingConsent::normalizePhone('0532 111 22 33'))->toBe('905321112233')
        ->and(MarketingConsent::normalizePhone('+90 532 111 22 33'))->toBe('905321112233')
        ->and(MarketingConsent::normalizePhone('5321112233'))->toBe('905321112233')
        ->and(MarketingConsent::normalizePhone(null))->toBeNull();
});

it('kimliksiz onay kaydedilmez', function () {
    expect(MarketingConsent::grant('email'))->toBeNull()
        ->and(MarketingConsent::count())->toBe(0);
});

it('cikis sayfasi giris gerektirmeden acilir', function () {
    // 6563: çıkış "kolay ve ücretsiz" olmalı. Giriş istemek çıkışı
    // zorlaştırmak sayılır.
    $onay = MarketingConsent::grant('email', email: 'cikis@example.com', source: 'register');

    $this->get(route('marketing.unsubscribe', $onay->unsubscribe_token))
        ->assertOk()
        ->assertSee('cikis@example.com')
        ->assertSee('E-posta');
});

it('cikis sayfasi arama motorlarina kapali', function () {
    $onay = MarketingConsent::grant('email', email: 'gizli@example.com', source: 'register');

    $this->get(route('marketing.unsubscribe', $onay->unsubscribe_token))
        ->assertOk()
        ->assertSee('name="robots" content="noindex, nofollow"', false);
});

it('tek kanaldan cikilabilir', function () {
    MarketingConsent::grant('email', email: 'coklu@example.com', source: 'register');
    $sms = MarketingConsent::grant('sms', email: 'coklu@example.com', phone: '05321112233', source: 'checkout');

    $this->post(route('marketing.unsubscribe.submit', $sms->unsubscribe_token), ['kanal' => 'sms'])
        ->assertRedirect();

    expect(MarketingConsent::channel('sms')->first()->status)->toBe('revoked')
        ->and(MarketingConsent::channel('email')->first()->status)->toBe('granted');
});

it('tumunden cikilabilir', function () {
    $eposta = MarketingConsent::grant('email', email: 'hepsi@example.com', source: 'register');
    MarketingConsent::grant('sms', email: 'hepsi@example.com', source: 'checkout');

    $this->post(route('marketing.unsubscribe.submit', $eposta->unsubscribe_token))
        ->assertRedirect();

    expect(MarketingConsent::granted()->count())->toBe(0)
        // Kayıt SİLİNMEZ: çıkış talebinin de ispatlanabilmesi gerekiyor.
        ->and(MarketingConsent::count())->toBe(2)
        ->and(MarketingConsent::first()->revoked_at)->not->toBeNull();
});

it('cikis sonrasi yeniden abone olunabilir', function () {
    $onay = MarketingConsent::grant('email', email: 'geri@example.com', source: 'register');
    $onay->revoke();

    $this->post(route('marketing.resubscribe', $onay->unsubscribe_token), ['kanal' => 'email'])
        ->assertRedirect();

    expect($onay->fresh()->status)->toBe('granted')
        ->and($onay->fresh()->revoked_at)->toBeNull();
});

it('gecersiz cikis anahtari 404 doner', function () {
    $this->get(route('marketing.unsubscribe', 'uydurma-anahtar'))->assertNotFound();
});

it('onay tazelenince iys yuklemesi sifirlanir', function () {
    // İYS'ye yüklenmiş bir onay değişirse yeniden yüklenmeli.
    $onay = MarketingConsent::grant('email', email: 'iys@example.com', source: 'register');
    $onay->update(['synced_to_iys_at' => now()]);

    $onay->revoke();

    expect($onay->fresh()->synced_to_iys_at)->toBeNull();
});

it('onay listesi admin panelinde acilir', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/admin/marketing-consents')
        ->assertOk();
});

it('kvkk metni ticari ileti onayini aciklar', function () {
    $this->get(route('kvkk'))
        ->assertOk()
        ->assertSee('Ticari elektronik ileti')
        ->assertSee('İleti Yönetim Sistemi')
        ->assertSee('6563');
});
