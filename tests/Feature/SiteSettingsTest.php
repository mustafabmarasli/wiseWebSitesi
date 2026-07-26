<?php

use App\Filament\Pages\ShippingSettings;
use App\Models\Setting;
use App\Models\User;

function ayarAdmin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

it('duyuru acilip kaydedilebilir', function () {
    Livewire::actingAs(ayarAdmin())
        ->test(ShippingSettings::class)
        ->set('data.announcement_enabled', true)
        ->set('data.announcement_title', 'Yakinda Satistayiz')
        ->set('data.announcement_text', 'Cok yakinda urun satisina baslayacaktir.')
        ->call('save')
        ->assertHasNoErrors();

    $s = Setting::current()->fresh();

    expect($s->announcement_enabled)->toBeTrue();
    expect($s->announcement_title)->toBe('Yakinda Satistayiz');
    expect($s->showsAnnouncement())->toBeTrue();
});

it('kargo ucreti zorunludur', function () {
    // Bos birakilirsa kaydetmemeli; kargo bedava icin 0 yazilmali
    Livewire::actingAs(ayarAdmin())
        ->test(ShippingSettings::class)
        ->set('data.standard_shipping_cost', '')
        ->call('save')
        ->assertHasErrors('data.standard_shipping_cost');
});

it('kargo ucreti sifir yazilarak bedava yapilabilir', function () {
    Livewire::actingAs(ayarAdmin())
        ->test(ShippingSettings::class)
        ->set('data.standard_shipping_cost', 0)
        ->call('save')
        ->assertHasNoErrors();

    expect((float) Setting::current()->fresh()->standard_shipping_cost)->toBe(0.0);
});

it('ucretsiz kargo limiti bos birakilabilir', function () {
    Livewire::actingAs(ayarAdmin())
        ->test(ShippingSettings::class)
        ->set('data.standard_shipping_cost', 49.90)
        ->set('data.free_shipping_threshold', '')
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::current()->fresh()->free_shipping_threshold)->toBeNull();
});

it('duyuru acikken metin bos ise uyarir', function () {
    Livewire::actingAs(ayarAdmin())
        ->test(ShippingSettings::class)
        ->set('data.announcement_enabled', true)
        ->set('data.announcement_text', '')
        ->call('save')
        ->assertHasErrors('data.announcement_text');
});

it('duyuru kapatilabilir', function () {
    Setting::current()->update([
        'announcement_enabled' => true,
        'announcement_text'    => 'Acik duyuru',
    ]);

    Livewire::actingAs(ayarAdmin())
        ->test(ShippingSettings::class)
        ->set('data.announcement_enabled', false)
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::current()->fresh()->announcement_enabled)->toBeFalse();
});

it('kargo ve duyuru birlikte kaydedilir', function () {
    Livewire::actingAs(ayarAdmin())
        ->test(ShippingSettings::class)
        ->set('data.standard_shipping_cost', 49.90)
        ->set('data.free_shipping_threshold', 500)
        ->set('data.announcement_enabled', true)
        ->set('data.announcement_title', 'Duyuru')
        ->set('data.announcement_text', 'Metin')
        ->call('save')
        ->assertHasNoErrors();

    $s = Setting::current()->fresh();

    expect((float) $s->standard_shipping_cost)->toBe(49.90);
    expect((float) $s->free_shipping_threshold)->toBe(500.0);
    expect($s->announcement_enabled)->toBeTrue();
});
