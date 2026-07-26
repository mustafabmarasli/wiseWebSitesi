<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name')->label('Ürün Adı'),
            ExportColumn::make('slug')->label('URL (slug)'),
            ExportColumn::make('category.name')->label('Kategori'),
            ExportColumn::make('description')->label('Açıklama'),
            ExportColumn::make('price')->label('Fiyat'),
            ExportColumn::make('eski_fiyat')->label('Eski Fiyat'),
            ExportColumn::make('discount_price')->label('İndirimli Fiyat'),
            ExportColumn::make('stock')->label('Stok'),
            ExportColumn::make('satis_sayisi')->label('Satış Adedi'),
            ExportColumn::make('view_count')->label('Görüntülenme'),
            ExportColumn::make('conversion_rate')
                ->label('Dönüşüm Oranı (%)')
                ->state(fn (Product $record): string => $record->conversion_rate === null
                    ? '-'
                    : (string) $record->conversion_rate),
            ExportColumn::make('rating')->label('Puan'),
            ExportColumn::make('image_path')->label('Ana Görsel Yolu'),
            ExportColumn::make('meta_title')->label('SEO Başlık'),
            ExportColumn::make('meta_description')->label('SEO Açıklama'),
            ExportColumn::make('created_at')->label('Oluşturulma'),
            ExportColumn::make('updated_at')->label('Güncellenme'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ürün dışa aktarımı tamamlandı: '
            . number_format($export->successful_rows, 0, ',', '.') . ' satır.';

        if ($failed = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failed, 0, ',', '.') . ' satır aktarılamadı.';
        }

        return $body;
    }
}
