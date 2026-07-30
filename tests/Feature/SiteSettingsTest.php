<?php

use App\Filament\Pages\ShippingSettings;
use App\Models\Setting;
use App\Models\User;

function ayarAdmin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

// Havale/EFT acikken hesap adi ve IBAN zorunludur; ayar sayfasinin diger
// bolumlerini sinayan testler formu gecerli bir baslangic durumuyla acmali.
beforeEach(fn () => havaleAyarla());

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

it('kargo ayarlari birlikte kaydedilir', function () {
    Livewire::actingAs(ayarAdmin())
        ->test(ShippingSettings::class)
        ->set('data.standard_shipping_cost', 49.90)
        ->set('data.free_shipping_threshold', 500)
        ->call('save')
        ->assertHasNoErrors();

    $s = Setting::current()->fresh();

    expect((float) $s->standard_shipping_cost)->toBe(49.90);
    expect((float) $s->free_shipping_threshold)->toBe(500.0);
});
