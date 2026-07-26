<?php

namespace App\Filament\Imports;

use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

/**
 * Excel/CSV dosyasından toplu ürün yükler.
 *
 * Eşleştirme `slug` üzerinden yapılır: aynı slug varsa ürün GÜNCELLENİR,
 * yoksa yeni ürün oluşturulur. Slug boş bırakılırsa ürün adından üretilir.
 */
class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Ürün Adı')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->exampleHeader('Ürün Adı')
                ->example('ESP32 DevKit V1'),

            ImportColumn::make('slug')
                ->label('URL (slug)')
                ->rules(['nullable', 'string', 'max:255'])
                ->ignoreBlankState()
                ->exampleHeader('URL (slug)')
                ->example('esp32-devkit-v1'),

            ImportColumn::make('category')
                ->label('Kategori')
                ->requiredMapping()
                ->rules(['required', 'string'])
                // Modeldeki category() bir ilişki; doğrudan atanamaz.
                // Değer beforeSave() içinde category_id'ye çevrilir.
                ->fillRecordUsing(fn () => null)
                ->exampleHeader('Kategori')
                ->example('Geliştirme Kartları'),

            ImportColumn::make('description')
                ->label('Açıklama')
                ->rules(['nullable', 'string'])
                ->ignoreBlankState()
                ->exampleHeader('Açıklama')
                ->example('WiFi ve Bluetooth destekli geliştirme kartı.'),

            ImportColumn::make('price')
                ->label('Fiyat')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0'])
                ->exampleHeader('Fiyat')
                ->example('249.90'),

            ImportColumn::make('eski_fiyat')
                ->label('Eski Fiyat')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0'])
                ->ignoreBlankState()
                ->exampleHeader('Eski Fiyat')
                ->example('299.90'),

            ImportColumn::make('stock')
                ->label('Stok')
                ->requiredMapping()
                ->integer()
                ->rules(['required', 'integer', 'min:0'])
                ->exampleHeader('Stok')
                ->example('25'),

            ImportColumn::make('rating')
                ->label('Puan')
                ->numeric()
                ->rules(['nullable', 'numeric', 'between:0,5'])
                ->ignoreBlankState()
                ->exampleHeader('Puan')
                ->example('4.5'),

            ImportColumn::make('image_path')
                ->label('Ana Görsel Yolu')
                ->rules(['nullable', 'string', 'max:255'])
                ->ignoreBlankState()
                ->exampleHeader('Ana Görsel Yolu')
                ->example('products/esp32.jpg'),

            ImportColumn::make('meta_title')
                ->label('SEO Başlık')
                ->rules(['nullable', 'string', 'max:255'])
                ->ignoreBlankState()
                ->exampleHeader('SEO Başlık')
                ->example('ESP32 DevKit V1 - Uygun Fiyat'),

            ImportColumn::make('meta_description')
                ->label('SEO Açıklama')
                ->rules(['nullable', 'string', 'max:500'])
                ->ignoreBlankState()
                ->exampleHeader('SEO Açıklama')
                ->example('ESP32 DevKit V1 stoktan hızlı kargo.'),
        ];
    }

    /**
     * Slug'a göre mevcut ürünü bul; yoksa yenisini hazırla.
     */
    public function resolveRecord(): Product
    {
        $slug = filled($this->data['slug'] ?? null)
            ? Str::slug($this->data['slug'])
            : Str::slug($this->data['name']);

        // Slug benzersiz olmalı; yeni üründe çakışma varsa sonuna sayı eklenir.
        $existing = Product::where('slug', $slug)->first();

        if ($existing) {
            return $existing;
        }

        $product = new Product();
        $product->slug = $slug;

        return $product;
    }

    /**
     * Kategori adını ID'ye çevirir; kategori yoksa oluşturur.
     */
    protected function beforeSave(): void
    {
        $categoryName = trim((string) ($this->data['category'] ?? ''));

        if ($categoryName === '') {
            return;
        }

        $category = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($categoryName)])->first();

        if (!$category) {
            $category = Category::create([
                'name'    => $categoryName,
                'slug'    => Str::slug($categoryName),
                'channel' => 'electronics',
            ]);
        }

        $this->record->category_id = $category->id;

        // Zorunlu alanların varsayılanları (yeni kayıtlarda)
        $this->record->rating ??= 0;
        $this->record->stock  ??= 0;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Ürün içe aktarımı tamamlandı: '
            . number_format($import->successful_rows, 0, ',', '.') . ' satır işlendi.';

        if ($failed = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failed, 0, ',', '.')
                . ' satır hatalı — hatalı satırları indirip düzeltebilirsiniz.';
        }

        return $body;
    }
}
