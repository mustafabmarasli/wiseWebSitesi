<?php

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockNotification;
use App\Models\User;

function filtreAdmin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

function filtreKategori(string $name = 'Kategori', string $channel = 'electronics'): Category
{
    return Category::create(['name' => $name, 'slug' => \Str::slug($name) . '-' . uniqid(), 'channel' => $channel]);
}

function filtreUrun(array $overrides = []): Product
{
    return Product::create(array_merge([
        'category_id' => filtreKategori()->id,
        'name'        => 'Filtre Urunu',
        'slug'        => 'filtre-urunu-' . uniqid(),
        'description' => 'Aciklama',
        'price'       => 100.00,
        'stock'       => 5,
        'rating'      => 5,
    ], $overrides));
}

it('kategori filtresi yalnizca o kategoriyi gosterir', function () {
    $kat1 = filtreKategori('Birinci');
    $kat2 = filtreKategori('Ikinci');
    $u1 = filtreUrun(['category_id' => $kat1->id, 'name' => 'Urun Bir']);
    $u2 = filtreUrun(['category_id' => $kat2->id, 'name' => 'Urun Iki']);

    Livewire::actingAs(filtreAdmin())
        ->test(ListProducts::class)
        ->filterTable('category_id', $kat1->id)
        ->assertCanSeeTableRecords([$u1])
        ->assertCanNotSeeTableRecords([$u2]);
});

it('marka filtresi calisir', function () {
    $dfrobot = filtreUrun(['brand' => 'DFRobot', 'name' => 'DFRobot Kart']);
    $espressif = filtreUrun(['brand' => 'Espressif', 'name' => 'Espressif Kart']);

    Livewire::actingAs(filtreAdmin())
        ->test(ListProducts::class)
        ->filterTable('brand', 'DFRobot')
        ->assertCanSeeTableRecords([$dfrobot])
        ->assertCanNotSeeTableRecords([$espressif]);
});

it('stok durumu tukendi filtresi calisir', function () {
    $tukenen = filtreUrun(['stock' => 0, 'name' => 'Tukenen']);
    $stokta = filtreUrun(['stock' => 20, 'name' => 'Stokta']);

    Livewire::actingAs(filtreAdmin())
        ->test(ListProducts::class)
        ->filterTable('stok_durumu', 'tukendi')
        ->assertCanSeeTableRecords([$tukenen])
        ->assertCanNotSeeTableRecords([$stokta]);
});

it('stok durumu az stok filtresi calisir', function () {
    $az = filtreUrun(['stock' => 5, 'name' => 'Az Stoklu']);
    $cok = filtreUrun(['stock' => 50, 'name' => 'Cok Stoklu']);

    Livewire::actingAs(filtreAdmin())
        ->test(ListProducts::class)
        ->filterTable('stok_durumu', 'az_stok')
        ->assertCanSeeTableRecords([$az])
        ->assertCanNotSeeTableRecords([$cok]);
});

it('vitrin filtresi calisir', function () {
    $vitrin = filtreUrun(['is_featured' => true, 'name' => 'Vitrindeki']);
    $normal = filtreUrun(['is_featured' => false, 'name' => 'Normal Urun']);

    Livewire::actingAs(filtreAdmin())
        ->test(ListProducts::class)
        ->filterTable('is_featured', true)
        ->assertCanSeeTableRecords([$vitrin])
        ->assertCanNotSeeTableRecords([$normal]);
});

it('indirimli filtresi yalnizca eski fiyati olani gosterir', function () {
    $indirimli = filtreUrun(['price' => 80, 'eski_fiyat' => 100, 'name' => 'Indirimli Urun']);
    $normal = filtreUrun(['price' => 100, 'eski_fiyat' => null, 'name' => 'Indirimsiz Urun']);

    Livewire::actingAs(filtreAdmin())
        ->test(ListProducts::class)
        ->filterTable('indirimli')
        ->assertCanSeeTableRecords([$indirimli])
        ->assertCanNotSeeTableRecords([$normal]);
});

it('fiyat araligi filtresi calisir', function () {
    $ucuz = filtreUrun(['price' => 50, 'name' => 'Ucuz Urun']);
    $orta = filtreUrun(['price' => 300, 'name' => 'Orta Urun']);
    $pahali = filtreUrun(['price' => 900, 'name' => 'Pahali Urun']);

    Livewire::actingAs(filtreAdmin())
        ->test(ListProducts::class)
        ->filterTable('fiyat_araligi', ['fiyat_min' => 100, 'fiyat_max' => 500])
        ->assertCanSeeTableRecords([$orta])
        ->assertCanNotSeeTableRecords([$ucuz, $pahali]);
});

it('stok bekleyen var filtresi calisir', function () {
    $bekleyenli = filtreUrun(['stock' => 0, 'name' => 'Bekleyenli Urun']);
    $bekleyensiz = filtreUrun(['stock' => 0, 'name' => 'Bekleyensiz Urun']);

    StockNotification::create(['product_id' => $bekleyenli->id, 'email' => 'bekleyen@example.com']);

    Livewire::actingAs(filtreAdmin())
        ->test(ListProducts::class)
        ->filterTable('stok_bekleyen_var')
        ->assertCanSeeTableRecords([$bekleyenli])
        ->assertCanNotSeeTableRecords([$bekleyensiz]);
});

it('marka ve barkod sutunlari aramada calisir', function () {
    $urun = filtreUrun(['brand' => 'DMV', 'gtin' => '1234567890123', 'name' => 'Aranan Urun']);

    Livewire::actingAs(filtreAdmin())
        ->test(ListProducts::class)
        ->searchTable('1234567890123')
        ->assertCanSeeTableRecords([$urun]);
});
