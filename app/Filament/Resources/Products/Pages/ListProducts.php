<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(ProductImporter::class)
                ->label('Excel ile Ürün Yükle')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->modalDescription(
                    'Excel (.xlsx) veya CSV dosyası yükleyin. Aynı URL (slug) değerine sahip ürünler '
                    . 'güncellenir, olmayanlar yeni ürün olarak eklenir. Örnek dosyayı indirip üzerine yazabilirsiniz.'
                ),

            ExportAction::make()
                ->exporter(ProductExporter::class)
                ->label('Excel İndir')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray'),

            CreateAction::make(),
        ];
    }
}
