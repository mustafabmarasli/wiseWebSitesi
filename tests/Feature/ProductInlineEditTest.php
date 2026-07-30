<?php

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Mail\BackInStockMail;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockNotification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function satirAdmin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

function satirUrunu(array $overrides = []): Product
{
    $category = Category::create([
        'name' => 'Kategori', 'slug' => 'kategori-' . uniqid(), 'channel' => 'electronics',
    ]);

    return Product::create(array_merge([
        'category_id' => $category->id,
        'name'        => 'Satir Urunu',
        'slug'        => 'satir-urunu-' . uniqid(),
        'description' => 'Aciklama',
        'price'       => 100.00,
        'stock'       => 5,
        'rating'      => 5,
    ], $overrides));
}

it('fiyat listeden guncellenebilir', function () {
    $product = satirUrunu();

    Livewire::actingAs(satirAdmin())
        ->test(ListProducts::class)
        ->call('updateTableColumnState', 'price', $product->getKey(), 149.90);

    expect((float) $product->fresh()->price)->toBe(149.90);
});

it('stok listeden guncellenebilir', function () {
    $product = satirUrunu(['stock' => 3]);

    Livewire::actingAs(satirAdmin())
        ->test(ListProducts::class)
        ->call('updateTableColumnState', 'stock', $product->getKey(), 42);

    expect($product->fresh()->stock)->toBe(42);
});

it('eski fiyat listeden guncellenebilir', function () {
    $product = satirUrunu();

    Livewire::actingAs(satirAdmin())
        ->test(ListProducts::class)
        ->call('updateTableColumnState', 'eski_fiyat', $product->getKey(), 199.00);

    expect((float) $product->fresh()->eski_fiyat)->toBe(199.00);
});

it('eski fiyat bos birakilinca null olur', function () {
    // Boş dize 0'a çevrilirse vitrinde "%100 indirim" çıkıyor.
    $product = satirUrunu(['eski_fiyat' => 199.00]);

    Livewire::actingAs(satirAdmin())
        ->test(ListProducts::class)
        ->call('updateTableColumnState', 'eski_fiyat', $product->getKey(), '');

    expect($product->fresh()->eski_fiyat)->toBeNull()
        ->and($product->fresh()->discount_percent)->toBeNull();
});

it('listeden stok girisi bekleyenlere e-posta gonderir', function () {
    Mail::fake();

    $product = satirUrunu(['stock' => 0]);
    StockNotification::create(['product_id' => $product->id, 'email' => 'bekleyen@example.com']);

    // Satır içi düzenleme de model olayı üretmeli; aksi hâlde stoğu listeden
    // giren yönetici bildirimlerin gitmediğini fark etmezdi.
    Livewire::actingAs(satirAdmin())
        ->test(ListProducts::class)
        ->call('updateTableColumnState', 'stock', $product->getKey(), 10);

    Mail::assertSent(BackInStockMail::class, 1);
    expect(StockNotification::whereNull('notified_at')->count())->toBe(0);
});

it('negatif stok listeden kaydedilemez', function () {
    $product = satirUrunu(['stock' => 5]);

    Livewire::actingAs(satirAdmin())
        ->test(ListProducts::class)
        ->call('updateTableColumnState', 'stock', $product->getKey(), -3);

    expect($product->fresh()->stock)->toBe(5);
});

it('satis sayisi artik zorunlu degil', function () {
    // Sistem bu sayacı hiçbir yerde artırmıyor; her kayıtta elle yazmaya
    // zorlamanın karşılığı yoktu.
    Livewire::actingAs(satirAdmin())
        ->test(CreateProduct::class)
        ->fillForm([
            'category_id' => Category::create(['name' => 'K', 'slug' => 'k-' . uniqid(), 'channel' => 'electronics'])->id,
            'name'        => 'Zorunluluk Testi',
            'slug'        => 'zorunluluk-testi',
            'price'       => 50,
            'stock'       => 1,
            'satis_sayisi' => null,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Product::where('slug', 'zorunluluk-testi')->first()->satis_sayisi)->toBe(0);
});

it('fiyat hala zorunlu', function () {
    Livewire::actingAs(satirAdmin())
        ->test(CreateProduct::class)
        ->fillForm([
            'category_id' => Category::create(['name' => 'K', 'slug' => 'k2-' . uniqid(), 'channel' => 'electronics'])->id,
            'name'        => 'Fiyatsiz',
            'slug'        => 'fiyatsiz',
            'stock'       => 1,
        ])
        ->call('create')
        ->assertHasFormErrors(['price']);
});
