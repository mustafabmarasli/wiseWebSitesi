<?php

use App\Filament\Pages\ShippingSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;

/** Telegram'ı yapılandırılmış say ve giden isteği yakala. */
function telegramAyarla(bool $yeniMusteriBildirimi): void
{
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.chat_id'   => '12345',
    ]);

    Setting::current()->update(['new_customer_telegram_enabled' => $yeniMusteriBildirimi]);

    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
}

it('ayar acikken yeni uyelikte telegram bildirimi gider', function () {
    telegramAyarla(yeniMusteriBildirimi: true);

    $this->post(route('register'), [
        'name'                  => 'Yeni Musteri',
        'email'                 => 'musteri@example.com',
        'password'              => 'sifre-1234',
        'password_confirmation' => 'sifre-1234',
        'kvkk_consent'          => '1',
    ])->assertSessionHas('success');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.telegram.org')
            && str_contains($request['text'], 'YENİ MÜŞTERİ KAYDI')
            && str_contains($request['text'], 'musteri@example.com');
    });
});

it('ayar kapaliyken bildirim gitmez', function () {
    telegramAyarla(yeniMusteriBildirimi: false);

    $this->post(route('register'), [
        'name'                  => 'Sessiz Musteri',
        'email'                 => 'sessiz@example.com',
        'password'              => 'sifre-1234',
        'password_confirmation' => 'sifre-1234',
        'kvkk_consent'          => '1',
    ])->assertSessionHas('success');

    Http::assertNothingSent();
});

it('varsayilan olarak bildirim kapalidir', function () {
    // Yayına alınınca aniden bildirim akmaya başlaması sürpriz olmamalı.
    expect(Setting::current()->notifiesNewCustomer())->toBeFalse();
});

it('yonetici hesabi olusmasi bildirim uretmez', function () {
    telegramAyarla(yeniMusteriBildirimi: true);

    User::factory()->create(['is_admin' => true]);

    Http::assertNothingSent();
});

it('bildirim mesajinda telefon gibi ek kisisel veri yer almaz', function () {
    telegramAyarla(yeniMusteriBildirimi: true);

    User::factory()->create(['name' => 'Ali Veli', 'email' => 'ali@example.com']);

    Http::assertSent(function ($request) {
        $metin = $request['text'];

        return str_contains($metin, 'Ali Veli')
            && str_contains($metin, 'ali@example.com')
            && ! str_contains($metin, 'password')
            && ! str_contains($metin, 'Telefon');
    });
});

it('telegram yapilandirilmamissa ayar acik olsa da gonderim denenmez', function () {
    config(['services.telegram.bot_token' => null, 'services.telegram.chat_id' => null]);
    Setting::current()->update(['new_customer_telegram_enabled' => true]);
    Http::fake();

    User::factory()->create();

    Http::assertNothingSent();
});

it('telegram hatasi uyelik islemini dusurmez', function () {
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.chat_id'   => '12345',
    ]);
    Setting::current()->update(['new_customer_telegram_enabled' => true]);

    // Telegram tarafı çökse bile müşteri hesabını açmış olmalı.
    Http::fake(['api.telegram.org/*' => Http::response('sunucu hatasi', 500)]);

    $this->post(route('register'), [
        'name'                  => 'Dayanikli Musteri',
        'email'                 => 'dayanikli@example.com',
        'password'              => 'sifre-1234',
        'password_confirmation' => 'sifre-1234',
        'kvkk_consent'          => '1',
    ])->assertSessionHas('success');

    expect(User::where('email', 'dayanikli@example.com')->exists())->toBeTrue();
    $this->assertAuthenticated();
});

it('misafir siparisinden uyelikte de bildirim gider', function () {
    telegramAyarla(yeniMusteriBildirimi: true);

    // Kullanıcı dört ayrı yerde oluşuyor; observer hepsini kapsıyor mu?
    User::create([
        'name'     => 'Misafirden Uye',
        'email'    => 'misafirden@example.com',
        'password' => bcrypt('sifre-1234'),
    ]);

    Http::assertSent(fn ($request) => str_contains($request['text'], 'Misafirden Uye'));
});

it('ayar panelden acilip kaydedilebilir', function () {
    // Ayarlar formu havale açıkken IBAN ve hesap adını zorunlu tutuyor;
    // kaydın geçmesi için bu alanların dolu olması gerekiyor.
    havaleAyarla();

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ShippingSettings::class)
        ->set('data.new_customer_telegram_enabled', true)
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::current()->fresh()->notifiesNewCustomer())->toBeTrue();
});

it('ayar panelden kapatilabilir', function () {
    Setting::current()->update(['new_customer_telegram_enabled' => true]);

    // Ayarlar formu havale açıkken IBAN ve hesap adını zorunlu tutuyor;
    // kaydın geçmesi için bu alanların dolu olması gerekiyor.
    havaleAyarla();

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(ShippingSettings::class)
        ->set('data.new_customer_telegram_enabled', false)
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::current()->fresh()->notifiesNewCustomer())->toBeFalse();
});
