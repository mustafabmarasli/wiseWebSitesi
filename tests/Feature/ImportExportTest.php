<?php

use App\Filament\Exports\ProductExporter;
use App\Filament\Exports\UserExporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function ieAdmin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

function ieCategory(string $name = 'Geliştirme Kartları'): Category
{
    return Category::create([
        'name' => $name, 'slug' => \Str::slug($name) . '-' . uniqid(), 'channel' => 'electronics',
    ]);
}

function ieProduct(array $overrides = []): Product
{
    return Product::create(array_merge([
        'category_id' => ieCategory()->id,
        'name' => 'ESP32 Kart', 'slug' => 'esp32-' . uniqid(),
        'description' => 'Aciklama', 'price' => 250.00, 'stock' => 8, 'rating' => 5,
    ], $overrides));
}

/** İçe aktarma işini çalıştırıp import kaydını döndürür. */
function runProductImport(string $csv): Import
{
    Storage::fake('local');

    $file = UploadedFile::fake()->createWithContent('urunler.csv', $csv);

    Livewire::actingAs(ieAdmin())
        ->test(ListProducts::class)
        ->callAction('import', data: [
            'file' => $file,
            'columnMap' => [
                'name' => 'Ürün Adı',
                'slug' => 'URL (slug)',
                'category' => 'Kategori',
                'description' => 'Açıklama',
                'price' => 'Fiyat',
                'eski_fiyat' => '',
                'stock' => 'Stok',
                'rating' => '',
                'image_path' => '',
                'meta_title' => '',
                'meta_description' => '',
            ],
        ]);

    return Import::latest('id')->firstOrFail();
}

it('urun listesinde excel yukleme ve indirme butonlari vardir', function () {
    $this->actingAs(ieAdmin())
        ->get('/admin/products')
        ->assertOk()
        ->assertSee('Excel ile Ürün Yükle')
        ->assertSee('Excel İndir');
});

it('kullanici listesinde excel indirme butonu vardir', function () {
    $this->actingAs(ieAdmin())
        ->get('/admin/users')
        ->assertOk()
        ->assertSee('Excel İndir');
});

it('urun disa aktarici gerekli sutunlari icerir', function () {
    $labels = collect(ProductExporter::getColumns())->map(fn ($c) => $c->getLabel())->all();

    expect($labels)->toContain('Ürün Adı', 'Kategori', 'Fiyat', 'Stok', 'Görüntülenme', 'Dönüşüm Oranı (%)');
});

it('kullanici disa aktarici parola alanlarini icermez', function () {
    $names = collect(UserExporter::getColumns())->map(fn ($c) => $c->getName())->all();

    expect($names)->not->toContain('password');
    expect($names)->not->toContain('remember_token');
    expect($names)->toContain('name', 'email', 'orders_count', 'total_spent');
});

it('csv den yeni urun olusturur', function () {
    $csv = "Ürün Adı,URL (slug),Kategori,Açıklama,Fiyat,Stok\n"
         . "Arduino Uno R3,arduino-uno-r3,Geliştirme Kartları,Klasik kart,349.90,15\n";

    $import = runProductImport($csv);

    expect($import->successful_rows)->toBe(1);

    $product = Product::where('slug', 'arduino-uno-r3')->first();

    expect($product)->not->toBeNull();
    expect($product->name)->toBe('Arduino Uno R3');
    expect((float) $product->price)->toBe(349.90);
    expect($product->stock)->toBe(15);
});

it('ayni slug ile gelen satir mevcut urunu gunceller', function () {
    $existing = ieProduct(['slug' => 'esp32-devkit', 'price' => 100.00, 'stock' => 1]);

    $csv = "Ürün Adı,URL (slug),Kategori,Açıklama,Fiyat,Stok\n"
         . "ESP32 DevKit Yeni,esp32-devkit,Geliştirme Kartları,Guncellendi,499.00,42\n";

    runProductImport($csv);

    $existing->refresh();

    expect(Product::where('slug', 'esp32-devkit')->count())->toBe(1);
    expect($existing->name)->toBe('ESP32 DevKit Yeni');
    expect((float) $existing->price)->toBe(499.00);
    expect($existing->stock)->toBe(42);
});

it('olmayan kategoriyi otomatik olusturur', function () {
    $csv = "Ürün Adı,URL (slug),Kategori,Açıklama,Fiyat,Stok\n"
         . "Yeni Sensor,yeni-sensor,Sensörler,Aciklama,49.90,10\n";

    runProductImport($csv);

    expect(Category::whereRaw('LOWER(name) = ?', ['sensörler'])->exists())->toBeTrue();
    expect(Product::where('slug', 'yeni-sensor')->first()->category->name)->toBe('Sensörler');
});

it('slug bos birakilirsa urun adindan uretilir', function () {
    $csv = "Ürün Adı,URL (slug),Kategori,Açıklama,Fiyat,Stok\n"
         . "Röle Modülü 5V,,Modüller,Aciklama,29.90,50\n";

    runProductImport($csv);

    expect(Product::where('name', 'Röle Modülü 5V')->first()->slug)->toBe('role-modulu-5v');
});

it('gecersiz satir reddedilir ve digerleri islenir', function () {
    $csv = "Ürün Adı,URL (slug),Kategori,Açıklama,Fiyat,Stok\n"
         . "Gecerli Urun,gecerli-urun,Kategori A,Aciklama,100.00,5\n"
         . ",eksik-isim,Kategori A,Aciklama,100.00,5\n"
         . "Negatif Fiyat,negatif-fiyat,Kategori A,Aciklama,-50,5\n";

    $import = runProductImport($csv);

    expect($import->successful_rows)->toBe(1);
    expect($import->getFailedRowsCount())->toBe(2);
    expect(Product::where('slug', 'gecerli-urun')->exists())->toBeTrue();
    expect(Product::where('slug', 'negatif-fiyat')->exists())->toBeFalse();
});
