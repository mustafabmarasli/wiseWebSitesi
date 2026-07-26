<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\URL;

/**
 * Sipariş sonuç sayfaları ve misafir üyelik akışının yetkilendirme testleri.
 */

function paidGuestOrder(array $overrides = []): Order
{
    return Order::create(array_merge([
        'user_id'      => null,
        'first_name'   => 'Test',
        'last_name'    => 'Kullanici',
        'email'        => 'misafir@example.com',
        'phone'        => '05551112233',
        'address'      => 'Test Mahallesi No:1',
        'city'         => 'Istanbul',
        'total_amount' => 100.00,
        'currency'     => 'TRY',
        'status'       => 'paid',
    ], $overrides));
}

it('imzasiz istekte siparis sayfasini gostermez', function () {
    $order = paidGuestOrder();

    $this->get(route('payment.success', $order->id))->assertForbidden();
});

it('gecerli imzali baglantiyla siparis sayfasini gosterir', function () {
    $order = paidGuestOrder();

    $url = URL::temporarySignedRoute('payment.success', now()->addDays(7), ['order' => $order->id]);

    $this->get($url)->assertOk();
});

it('kurcalanmis imzayi reddeder', function () {
    $order = paidGuestOrder();

    $url = URL::temporarySignedRoute('payment.success', now()->addDays(7), ['order' => $order->id]);
    $tampered = preg_replace('/signature=[0-9a-f]+/', 'signature=' . str_repeat('a', 64), $url);

    $this->get($tampered)->assertForbidden();
});

it('suresi gecmis imzayi reddeder', function () {
    $order = paidGuestOrder();

    $url = URL::temporarySignedRoute('payment.success', now()->addMinute(), ['order' => $order->id]);

    $this->travel(2)->minutes();

    $this->get($url)->assertForbidden();
});

it('baska bir kullanicinin siparisini giris yapmis kullaniciya gostermez', function () {
    $owner    = User::factory()->create();
    $attacker = User::factory()->create();
    $order    = paidGuestOrder(['user_id' => $owner->id]);

    $this->actingAs($attacker)
        ->get(route('payment.success', $order->id))
        ->assertForbidden();
});

it('siparis sahibine giris yapmisken gosterir', function () {
    $owner = User::factory()->create();
    $order = paidGuestOrder(['user_id' => $owner->id]);

    $this->actingAs($owner)
        ->get(route('payment.success', $order->id))
        ->assertOk();
});

it('yetki kaniti olmadan misafir siparisinden hesap acilmasini engeller', function () {
    $order = paidGuestOrder();

    $this->post(route('payment.register-guest', $order->id), [
        'password'              => 'cokgizlisifre',
        'password_confirmation' => 'cokgizlisifre',
    ])->assertForbidden();

    expect(User::where('email', $order->email)->exists())->toBeFalse();
});

it('mevcut bir hesaba sifre bilmeden giris yaptirmaz', function () {
    $victim = User::factory()->create(['email' => 'kurban@example.com']);
    $order  = paidGuestOrder(['email' => 'kurban@example.com']);

    // Saldirgan siparise erisim kanitina sahip olsa bile hesaba giremez.
    $this->withSession(['order_access' => [$order->id]])
        ->post(route('payment.register-guest', $order->id), [
            'password'              => 'saldirganinSifresi',
            'password_confirmation' => 'saldirganinSifresi',
        ])
        ->assertRedirect(route('login'));

    expect(auth()->check())->toBeFalse();
    expect($order->fresh()->user_id)->toBeNull();
    // Kurbanin sifresi degismemis olmali
    expect(Hash::check('saldirganinSifresi', $victim->fresh()->password))->toBeFalse();
});

it('erisim kaniti olan misafir icin yeni hesap acar', function () {
    $order = paidGuestOrder();

    $this->withSession(['order_access' => [$order->id]])
        ->post(route('payment.register-guest', $order->id), [
            'password'              => 'yenisifre123',
            'password_confirmation' => 'yenisifre123',
        ])
        ->assertRedirect(route('profile.index'));

    $user = User::where('email', $order->email)->first();

    expect($user)->not->toBeNull();
    expect($order->fresh()->user_id)->toBe($user->id);
    expect(auth()->id())->toBe($user->id);
});
