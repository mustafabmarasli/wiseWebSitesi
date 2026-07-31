<?php

use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function trackingOrder(array $overrides = []): Order
{
    return Order::create(array_merge([
        'first_name' => 'Test', 'last_name' => 'Kullanici',
        'email' => 'musteri@example.com', 'phone' => '05551112233',
        'address' => 'Test Sokak No:1', 'city' => 'Istanbul',
        'total_amount' => 500.00, 'currency' => 'TRY', 'status' => 'paid',
    ], $overrides));
}

it('takip numarasi girilmemis siparis hasTracking false doner', function () {
    $order = trackingOrder();

    expect($order->hasTracking())->toBeFalse();
});

it('takip numarasi girilince hasTracking true doner', function () {
    $order = trackingOrder(['tracking_number' => 'YK123456789']);

    expect($order->hasTracking())->toBeTrue();
});

it('durum kargoya verildiye gecince musteriye e-posta gider', function () {
    Mail::fake();

    $order = trackingOrder(['status' => 'paid', 'tracking_number' => 'YK123456789']);
    $order->update(['status' => 'shipped']);

    Mail::assertSent(OrderShippedMail::class, fn ($mail) => $mail->hasTo('musteri@example.com'));
    expect($order->fresh()->shipped_notified_at)->not->toBeNull();
});

it('ayni siparis ikinci kez kaydedilince tekrar e-posta gitmez', function () {
    Mail::fake();

    $order = trackingOrder(['status' => 'paid']);
    $order->update(['status' => 'shipped']);

    // Takip linkini sonradan eklemek gibi bir düzenleme daha — ikinci
    // e-posta GİTMEMELİ, aksi hâlde her düzenlemede müşteri tekrar bildirim alır.
    $order->update(['tracking_url' => 'https://example.com/takip/123']);

    Mail::assertSent(OrderShippedMail::class, 1);
});

it('durum degismeden yapilan guncelleme e-posta gondermez', function () {
    Mail::fake();

    $order = trackingOrder(['status' => 'shipped', 'shipped_notified_at' => now()]);
    $order->update(['shipping_cost' => 25.00]);

    Mail::assertNothingSent();
});

it('takip numarasi olmadan da kargoya verildi e-postasi gider', function () {
    // Takip numarasını beklemek bildirimi geciktirmemeli.
    Mail::fake();

    $order = trackingOrder(['status' => 'paid']);
    $order->update(['status' => 'shipped']);

    Mail::assertSent(OrderShippedMail::class, 1);
});

it('e-postada takip linki varsa buton cikar linki yoksa cikmaz', function () {
    $linkli = trackingOrder(['tracking_number' => 'YK1', 'tracking_url' => 'https://example.com/takip/1']);
    $linksiz = trackingOrder(['tracking_number' => 'YK2', 'email' => 'linksiz@example.com']);

    $htmlLinkli = (new OrderShippedMail($linkli))->render();
    $htmlLinksiz = (new OrderShippedMail($linksiz))->render();

    expect($htmlLinkli)->toContain('https://example.com/takip/1')
        ->and($htmlLinkli)->toContain('YK1')
        ->and($htmlLinksiz)->toContain('YK2')
        ->and($htmlLinksiz)->not->toContain('Kargomu Takip Et');
});

it('panelden durum kargoya verildi yapilinca takip bilgisiyle kaydedilir', function () {
    Mail::fake();

    $order = trackingOrder(['status' => 'paid']);
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(EditOrder::class, ['record' => $order->getKey()])
        ->fillForm([
            'status'          => 'shipped',
            'tracking_number' => 'ARS987654321',
            'tracking_url'    => 'https://www.araskargo.com.tr/takip/ARS987654321',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $order->refresh();

    expect($order->status)->toBe('shipped')
        ->and($order->tracking_number)->toBe('ARS987654321')
        ->and($order->shipped_notified_at)->not->toBeNull();

    Mail::assertSent(OrderShippedMail::class, fn ($mail) => $mail->hasTo($order->email));
});

it('musteri siparis detayinda takip numarasi ve linkini gorur', function () {
    $user = User::factory()->create();
    $order = trackingOrder([
        'user_id'         => $user->id,
        'status'          => 'shipped',
        'tracking_number' => 'YK555666777',
        'tracking_url'    => 'https://www.yurticikargo.com/tr/online-servisler/gonderi-sorgula?code=YK555666777',
    ]);

    $this->actingAs($user)
        ->get(route('profile.order-detail', $order->id))
        ->assertOk()
        ->assertSee('YK555666777')
        ->assertSee('Kargomu Takip Et')
        ->assertSee('https://www.yurticikargo.com/tr/online-servisler/gonderi-sorgula?code=YK555666777', false);
});

it('takip numarasi yoksa musteri siparis detayinda kutu cikmaz', function () {
    $user = User::factory()->create();
    $order = trackingOrder(['user_id' => $user->id, 'status' => 'paid']);

    $this->actingAs($user)
        ->get(route('profile.order-detail', $order->id))
        ->assertOk()
        ->assertDontSee('Kargo Takip Numarası');
});

it('baskasinin siparis detayini goremez', function () {
    $sahip = User::factory()->create();
    $baskasi = User::factory()->create();
    $order = trackingOrder(['user_id' => $sahip->id]);

    $this->actingAs($baskasi)
        ->get(route('profile.order-detail', $order->id))
        ->assertForbidden();
});
