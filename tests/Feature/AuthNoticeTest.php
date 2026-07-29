<?php

use App\Models\User;

it('giris yapinca bildirim mesaji gonderilir', function () {
    $user = User::factory()->create(['password' => bcrypt('sifre-1234')]);

    $this->post(route('login'), ['email' => $user->email, 'password' => 'sifre-1234'])
        ->assertSessionHas('success', 'Başarıyla giriş yaptınız.');

    $this->assertAuthenticatedAs($user);
});

it('hatali girise basarili bildirimi cikmaz', function () {
    $user = User::factory()->create(['password' => bcrypt('sifre-1234')]);

    $this->post(route('login'), ['email' => $user->email, 'password' => 'yanlis'])
        ->assertSessionMissing('success')
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('uye olunca bildirim mesaji gonderilir', function () {
    $this->post(route('register'), [
        'name'                  => 'Yeni Uye',
        'email'                 => 'yeni@example.com',
        'password'              => 'sifre-1234',
        'password_confirmation' => 'sifre-1234',
        'kvkk_consent'          => '1',
    ])->assertSessionHas('success');

    $this->assertAuthenticated();
});

it('cikis yapinca bildirim mesaji gonderilir', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('logout'))
        ->assertSessionHas('success', 'Başarıyla çıkış yapıldı.');

    $this->assertGuest();
});

it('giris sonrasi acilan sayfada bildirim gosterilir', function () {
    $user = User::factory()->create(['password' => bcrypt('sifre-1234')]);

    // Giriş `landing` sayfasına yönlendirir; o sayfa `layouts.app` kullanmıyor.
    // Bildirim kutusu orada yoksa mesaj üretilir ama hiç görünmez.
    $html = $this->followingRedirects()
        ->post(route('login'), ['email' => $user->email, 'password' => 'sifre-1234'])
        ->assertOk()
        ->getContent();

    expect($html)->toContain('Başarıyla giriş yaptınız.')
        ->and($html)->toContain('id="app-toast"');
});

it('bildirim kutusu sag ustte konumlanir', function () {
    $this->get(route('electronics.home'))
        ->assertOk()
        ->assertSee('id="app-toast"', false)
        ->assertSee('fixed top-5 right-5 left-5 sm:left-auto', false);
});

it('cikis sonrasi acilan sayfada bildirim gosterilir', function () {
    $html = $this->actingAs(User::factory()->create())
        ->followingRedirects()
        ->post(route('logout'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('Başarıyla çıkış yapıldı.');
});
