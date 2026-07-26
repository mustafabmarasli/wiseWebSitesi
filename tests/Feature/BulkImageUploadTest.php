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

it('galeri secilirse ek gorsellere ekler', function () {
    $product = bulkProduct('esp32-devkit');

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->set('asGallery', true)
        ->set('files', [fakeWebp('esp32-devkit.webp')])
        ->call('save');

    $product->refresh();

    expect($product->image_path)->toBeNull();
    expect($product->additional_images)->toHaveCount(1);
});

it('numarali dosya adi ayni urune eslesir', function () {
    $product = bulkProduct('esp32-devkit');

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->set('asGallery', true)
        ->set('files', [fakeWebp('esp32-devkit-2.webp'), fakeWebp('esp32-devkit-3.webp')])
        ->call('save');

    expect($product->fresh()->additional_images)->toHaveCount(2);
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

it('bosluklu dosya adi slug e cevrilir', function () {
    $product = bulkProduct('esp32-dev-kit');

    Livewire::actingAs(User::factory()->create(['is_admin' => true]))
        ->test(BulkImageUpload::class)
        ->set('files', [fakeWebp('ESP32 Dev Kit.webp')])
        ->call('save');

    expect($product->fresh()->image_path)->not->toBeNull();
});
