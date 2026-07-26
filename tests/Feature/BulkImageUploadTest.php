<?php

use App\Filament\Pages\BulkImageUpload;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function bulkProduct(string $slug, string $name = 'Test Urun'): Product
{
    $category = Category::firstOrCreate(
        ['slug' => 'toplu-test'],
        ['name' => 'Toplu Test', 'channel' => 'electronics'],
    );

    return Product::create([
        'category_id' => $category->id,
        'name'        => $name,
        'slug'        => $slug,
        'description' => 'Aciklama',
        'price'       => 100.00,
        'stock'       => 5,
        'rating'      => 5,
    ]);
}

/** Sahte bir webp dosyasi (GD gerektirmez). */
function fakeWebp(string $filename): UploadedFile
{
    return UploadedFile::fake()->createWithContent($filename, 'sahte-webp-icerigi');
}

beforeEach(function () {
    Storage::fake('public');
});

it('sayfa admin panelinde acilir', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/admin/bulk-image-upload')
        ->assertOk()
        ->assertSee('Toplu Görsel Yükle');
});

it('admin olmayan kullanici giremez', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get('/admin/bulk-image-upload')
        ->assertForbidden();
});

it('dosya adiyla eslesen urune ana gorsel atar', function () {
    $product = bulkProduct('esp32-devkit');

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->set('files', [fakeWebp('esp32-devkit.webp')])
        ->call('save');

    $product->refresh();

    expect($product->image_path)->toStartWith('products/');
    Storage::disk('public')->assertExists($product->image_path);
});

it('galeri modunda ek gorsellere ekler', function () {
    $product = bulkProduct('esp32-devkit');

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->set('mode', 'gallery')
        ->set('files', [fakeWebp('esp32-devkit.webp')])
        ->call('save');

    $product->refresh();

    expect($product->image_path)->toBeNull();
    expect($product->additional_images)->toHaveCount(1);
});

it('ayni urune 5 gorsel: ilki ana gorsel kalani galeri', function () {
    $product = bulkProduct('esp32-devkit');

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->set('mode', 'auto')
        ->set('files', [
            fakeWebp('esp32-devkit.webp'),
            fakeWebp('esp32-devkit-2.webp'),
            fakeWebp('esp32-devkit-3.webp'),
            fakeWebp('esp32-devkit-4.webp'),
            fakeWebp('esp32-devkit-5.webp'),
        ])
        ->call('save');

    $product->refresh();

    expect($product->image_path)->not->toBeNull();
    expect($product->additional_images)->toHaveCount(4);
});

it('otomatik modda sira karisik gelse de numarasiz olan ana gorsel olur', function () {
    $product = bulkProduct('esp32-devkit');

    // Tarayici dosyalari alfabetik siralamayabilir
    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->set('mode', 'auto')
        ->set('files', [
            fakeWebp('esp32-devkit-3.webp'),
            fakeWebp('esp32-devkit.webp'),
            fakeWebp('esp32-devkit-2.webp'),
        ])
        ->call('save');

    $product->refresh();

    expect($product->image_path)->not->toBeNull();
    expect($product->additional_images)->toHaveCount(2);
    expect($product->additional_images)->not->toContain($product->image_path);
});

it('tek seferde birden fazla urunun gorselleri islenir', function () {
    $a = bulkProduct('urun-a', 'Urun A');
    $b = bulkProduct('urun-b', 'Urun B');

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->set('mode', 'auto')
        ->set('files', [
            fakeWebp('urun-a.webp'), fakeWebp('urun-a-2.webp'),
            fakeWebp('urun-b.webp'), fakeWebp('urun-b-2.webp'), fakeWebp('urun-b-3.webp'),
        ])
        ->call('save');

    expect($a->fresh()->additional_images)->toHaveCount(1);
    expect($b->fresh()->additional_images)->toHaveCount(2);
    expect($a->fresh()->image_path)->not->toBeNull();
    expect($b->fresh()->image_path)->not->toBeNull();
});

it('galeriyi temizle secilirse eski gorseller silinir', function () {
    $product = bulkProduct('esp32-devkit');
    $product->update(['additional_images' => ['products/eski1.webp', 'products/eski2.webp']]);

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->set('mode', 'gallery')
        ->set('replaceGallery', true)
        ->set('files', [fakeWebp('esp32-devkit-2.webp')])
        ->call('save');

    $gallery = $product->fresh()->additional_images;

    expect($gallery)->toHaveCount(1);
    expect($gallery)->not->toContain('products/eski1.webp');
});

it('adi rakamla biten urun bozulmaz', function () {
    // "esp32-c6" gibi slug'lar numara sanilip kirpilmamali
    $product = bulkProduct('esp32-c6');

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->set('files', [fakeWebp('esp32-c6.webp')])
        ->call('save');

    expect($product->fresh()->image_path)->not->toBeNull();
});

it('eslesmeyen dosya icin hata bildirir ve digerlerini isler', function () {
    $product = bulkProduct('esp32-devkit');

    $component = Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->set('files', [fakeWebp('esp32-devkit.webp'), fakeWebp('olmayan-urun.webp')])
        ->call('save');

    $results = $component->get('results');

    expect($results)->toHaveCount(2);
    expect(collect($results)->where('status', 'ok'))->toHaveCount(1);
    expect(collect($results)->where('status', 'error')->first()['message'])
        ->toContain('bulunamadı');

    // Eslesen urun yine de kaydedilmis olmali
    expect($product->fresh()->image_path)->not->toBeNull();
});

it('urun referans listesi beklenen dosya adlarini gosterir', function () {
    bulkProduct('esp32-devkit-v1', 'ESP32 DevKit V1');

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get('/admin/bulk-image-upload')
        ->assertOk()
        ->assertSee('ESP32 DevKit V1')
        ->assertSee('esp32-devkit-v1.jpg');
});

it('gorseli olmayan urunler listede once gelir', function () {
    $gorselli = bulkProduct('gorselli-urun', 'Gorselli Urun');
    $gorselli->update(['image_path' => 'products/var.webp']);

    bulkProduct('gorselsiz-urun', 'Gorselsiz Urun');

    $liste = Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->instance()
        ->getProductReference();

    expect($liste->first()['dosya'])->toBe('gorselsiz-urun.jpg');
    expect($liste->first()['gorselVar'])->toBeFalse();
    expect($liste->last()['gorselVar'])->toBeTrue();
});

it('bosluklu dosya adi slug e cevrilir', function () {
    $product = bulkProduct('esp32-dev-kit');

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->set('files', [fakeWebp('ESP32 Dev Kit.webp')])
        ->call('save');

    expect($product->fresh()->image_path)->not->toBeNull();
});
