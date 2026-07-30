<?php

use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\CampaignDelivery;
use App\Models\MarketingConsent;
use App\Models\Setting;
use App\Models\User;
use App\Services\CampaignSender;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

function gonderimAc(): void
{
    Setting::current()->update(['marketing_sending_enabled' => true]);
}

function netgsmAyarla(): void
{
    config([
        'services.netgsm.usercode' => '8501112233',
        'services.netgsm.password' => 'gizli',
        'services.netgsm.header'   => 'WISE',
    ]);
}

function kampanya(array $overrides = []): Campaign
{
    return Campaign::create(array_merge([
        'channel' => 'email',
        'title'   => 'Test Kampanyasi',
        'subject' => 'Kampanya Basligi',
        'body'    => '<p>Indirim var.</p>',
        'status'  => 'draft',
    ], $overrides));
}

it('ana salter kapaliyken gonderim yapilmaz', function () {
    Mail::fake();
    MarketingConsent::grant('email', email: 'onayli@example.com', source: 'register');

    $sonuc = (new CampaignSender())->send(kampanya());

    expect($sonuc['sent'])->toBe(0);
    Mail::assertNothingSent();
});

it('salter acikken onaylilara gonderilir', function () {
    Mail::fake();
    gonderimAc();

    MarketingConsent::grant('email', email: 'onayli@example.com', source: 'register');

    $sonuc = (new CampaignSender())->send(kampanya());

    expect($sonuc['sent'])->toBe(1);
    Mail::assertSent(CampaignMail::class, fn ($mail) => $mail->hasTo('onayli@example.com'));
});

it('onayini geri cekmis kisiye gonderilmez', function () {
    Mail::fake();
    gonderimAc();

    MarketingConsent::grant('email', email: 'giden@example.com', source: 'register');
    MarketingConsent::grant('email', email: 'cikan@example.com', source: 'register')->revoke();

    (new CampaignSender())->send(kampanya());

    Mail::assertSent(CampaignMail::class, 1);
    Mail::assertSent(CampaignMail::class, fn ($mail) => $mail->hasTo('giden@example.com'));
});

it('hic onayli yoksa gonderim engellenir', function () {
    gonderimAc();

    expect((new CampaignSender())->blocker(kampanya()))
        ->toContain('onaylı alıcı yok');
});

it('baska kanalin onayina gonderilmez', function () {
    Mail::fake();
    gonderimAc();

    // Yalnızca SMS onayı vermiş kişiye e-posta kampanyası gitmemeli.
    MarketingConsent::grant('sms', email: 'sadecesms@example.com', phone: '05321112233', source: 'checkout');

    $sonuc = (new CampaignSender())->send(kampanya(['channel' => 'email']));

    expect($sonuc['sent'])->toBe(0);
    Mail::assertNothingSent();
});

it('e-postaya cikis baglantisi otomatik eklenir', function () {
    gonderimAc();
    $onay = MarketingConsent::grant('email', email: 'cikis@example.com', source: 'register');

    // Çıkış bağlantısı kanunen zorunlu; panelde yazana bırakılmamalı.
    $html = (new CampaignMail(kampanya(), $onay))->render();

    expect($html)->toContain($onay->unsubscribe_token)
        ->and($html)->toContain('Abonelikten çık');
});

it('sms metnine cikis baglantisi eklenir', function () {
    gonderimAc();
    netgsmAyarla();
    Http::fake(['api.netgsm.com.tr/*' => Http::response('00 123456')]);

    $onay = MarketingConsent::grant('sms', phone: '05321112233', source: 'checkout');

    (new CampaignSender())->send(kampanya(['channel' => 'sms', 'body' => 'Indirim basladi']));

    Http::assertSent(fn ($request) => str_contains($request['message'], 'Indirim basladi')
        && str_contains($request['message'], $onay->unsubscribe_token));
});

it('netgsm yapilandirilmamissa sms gonderimi engellenir', function () {
    gonderimAc();
    config(['services.netgsm.usercode' => null, 'services.netgsm.password' => null, 'services.netgsm.header' => null]);
    MarketingConsent::grant('sms', phone: '05321112233', source: 'checkout');

    expect((new CampaignSender())->blocker(kampanya(['channel' => 'sms'])))
        ->toContain('Netgsm yapılandırılmamış');
});

it('netgsm hata kodu anlasilir mesaja cevrilir', function () {
    gonderimAc();
    netgsmAyarla();
    // 30 = kullanıcı adı/şifre hatalı
    Http::fake(['api.netgsm.com.tr/*' => Http::response('30')]);

    MarketingConsent::grant('sms', phone: '05321112233', source: 'checkout');

    $sonuc = (new CampaignSender())->send(kampanya(['channel' => 'sms', 'body' => 'Deneme']));

    expect($sonuc['failed'])->toBe(1)
        ->and(CampaignDelivery::first()->error)->toContain('şifre hatalı');
});

it('ayni kampanyada ayni kisiye ikinci kez gonderilmez', function () {
    Mail::fake();
    gonderimAc();

    $kampanya = kampanya();
    MarketingConsent::grant('email', email: 'tek@example.com', source: 'register');

    // Komut yarıda kalıp tekrar çalıştırılırsa kaldığı yerden devam etmeli.
    (new CampaignSender())->send($kampanya);
    $kampanya->update(['status' => 'queued']);
    (new CampaignSender())->send($kampanya);

    Mail::assertSent(CampaignMail::class, 1);
    expect(CampaignDelivery::count())->toBe(1);
});

it('gonderim kaydi tutulur', function () {
    Mail::fake();
    gonderimAc();
    MarketingConsent::grant('email', email: 'kayit@example.com', source: 'register');

    $kampanya = kampanya();
    (new CampaignSender())->send($kampanya);

    $kayit = CampaignDelivery::first();

    expect($kayit->contact)->toBe('kayit@example.com')
        ->and($kayit->status)->toBe('sent')
        ->and($kayit->sent_at)->not->toBeNull()
        ->and($kampanya->fresh()->status)->toBe('sent')
        ->and($kampanya->fresh()->completed_at)->not->toBeNull();
});

it('deneme gonderimi onay aramaz ve kayit acmaz', function () {
    Mail::fake();
    gonderimAc();

    // Hedef yöneticinin kendisi; müşteri listesine dokunmamalı.
    $sonuc = (new CampaignSender())->sendTest(kampanya(), 'yonetici@example.com');

    expect($sonuc['ok'])->toBeTrue()
        ->and(CampaignDelivery::count())->toBe(0);

    Mail::assertSent(CampaignMail::class, fn ($mail) => $mail->hasTo('yonetici@example.com'));
});

it('komut sirada bekleyen kampanyayi gonderir', function () {
    Mail::fake();
    gonderimAc();
    MarketingConsent::grant('email', email: 'komut@example.com', source: 'register');

    $kampanya = kampanya(['status' => 'queued']);

    $this->artisan('campaigns:send')->assertSuccessful();

    expect($kampanya->fresh()->status)->toBe('sent')
        ->and($kampanya->fresh()->sent_count)->toBe(1);
});

it('taslak kampanya komutla gonderilmez', function () {
    Mail::fake();
    gonderimAc();
    MarketingConsent::grant('email', email: 'taslak@example.com', source: 'register');

    kampanya(['status' => 'draft']);

    $this->artisan('campaigns:send')->assertSuccessful();

    Mail::assertNothingSent();
});

it('kampanya yonetimi admin panelinde acilir', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/admin/campaigns')
        ->assertOk();
});

it('varsayilan olarak toplu gonderim kapalidir', function () {
    // Kodun yayına alınmasıyla gönderim kendiliğinden açılmamalı.
    expect(Setting::current()->marketing_sending_enabled)->toBeFalse();
});
