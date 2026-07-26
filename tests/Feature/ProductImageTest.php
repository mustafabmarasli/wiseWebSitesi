<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function imageProduct(array $overrides = []): Product
{
    $category = Category::create([
        'name'    => 'Test Kategori',
        'slug'    => 'test-kategori-' . uniqid(),
        'channel' => 'electronics',
    ]);

    return Product::create(array_merge([
        'category_id' => $category->id,
        'name'        => 'Test Urun',
        'slug'        => 'test-urun-' . uniqid(),
        'description' => 'Aciklama',
        'price'       => 100.00,
        'stock'       => 10,
        'rating'      => 5,
    ], $overrides));
}

it('panelden yuklenen gorseli cozer', function () {
    Storage::fake('public');
    Storage::disk('public')->put('products/yuklenen.jpg', 'sahte-icerik');

    $product = imageProduct(['image_path' => 'products/yuklenen.jpg']);

    expect($product->image_url)->toContain('products/yuklenen.jpg');
});

it('seeder formatindaki public gorseli cozer', function () {
    // public/img altinda gercekten var olan bir seeder yolu
    $existing = collect(glob(public_path('img/*/*.jpg')))->first();

    expect($existing)->not->toBeNull('public/img altinda test icin gorsel bulunamadi');

    $relative = str_replace('\\', '/', substr($existing, strlen(public_path()) + 1));
    $product  = imageProduct(['image_path' => $relative]);

    expect($product->image_url)->toBe(asset($relative));
});

it('olmayan gorsel icin null doner', function () {
    Storage::fake('public');

    $product = imageProduct(['image_path' => 'products/hic-yok.jpg']);

    expect($product->image_url)->toBeNull();
});

it('gorseli olmayan urun icin null doner', function () {
    expect(imageProduct(['image_path' => null])->image_url)->toBeNull();
});

it('galeri gorsellerinden cozulemeyenler atlanir', function () {
    Storage::fake('public');
    Storage::disk('public')->put('products/var.jpg', 'sahte');

    $product = imageProduct([
        'image_path'        => 'products/var.jpg',
        'additional_images' => ['products/var.jpg', 'products/yok.jpg'],
    ]);

    expect($product->additional_image_urls)->toHaveCount(1);
});

it('urun detay sayfasi yuklenen gorseli gosterir', function () {
    Storage::fake('public');
    Storage::disk('public')->put('products/detay.jpg', 'sahte');

    $product = imageProduct(['image_path' => 'products/detay.jpg']);

    // @js() cikti icinde slash'leri escape ettigi icin dosya adiyla ariyoruz
    $this->get(route('product.detail', $product->slug))
        ->assertOk()
        ->assertSee('detay.jpg', escape: false);
});

it('gorsel yukleme alanlari public diske ayarlidir', function () {
    $uploads = collect(
        \App\Filament\Resources\Products\Schemas\ProductForm::configure(\Filament\Schemas\Schema::make())
            ->getComponents()
    )->filter(fn ($c) => $c instanceof \Filament\Forms\Components\FileUpload);

    expect($uploads)->not->toBeEmpty();

    foreach ($uploads as $upload) {
        expect($upload->getDiskName())->toBe('public',
            "\"{$upload->getName()}\" varsayilan diske yaziyor; panelden yuklenen gorseller sitede gorunmez.");
    }
});
