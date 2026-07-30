<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Exports\ProductExporter;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                // Fiyat, eski fiyat ve stok LİSTEDEN düzenlenebilir: her ürün
                // için forma girip çıkmak, fiyat/stok güncellemesi gibi sık
                // yapılan bir işte gereksiz yol.
                //
                // DİKKAT: Buradan yapılan kayıt da model olayı üretir; yani
                // stoğu 0'dan yukarı çekmek bekleyenlere e-posta gönderir
                // (bkz. GELISTIRICI-NOTLARI madde 10.6). Bu kasıtlı.
                TextInputColumn::make('price')
                    ->label('Fiyat ₺')
                    ->type('number')
                    ->rules(['required', 'numeric', 'min:0'])
                    ->extraInputAttributes(['step' => '0.01', 'min' => '0', 'style' => 'width:6.5rem'])
                    ->sortable(),

                TextInputColumn::make('eski_fiyat')
                    ->label('Eski Fiyat ₺')
                    // Boş bırakılabilir: indirim yoksa üstü çizili fiyat
                    // gösterilmez. Boş dize null'a çevrilmezse 0,00 TL olarak
                    // kaydediliyor ve vitrinde "%100 indirim" çıkıyordu.
                    ->rules(['nullable', 'numeric', 'min:0'])
                    ->updateStateUsing(function (Product $record, $state) {
                        $record->update(['eski_fiyat' => filled($state) ? $state : null]);
                    })
                    ->type('number')
                    ->extraInputAttributes(['step' => '0.01', 'min' => '0', 'style' => 'width:6.5rem'])
                    ->sortable(),

                TextInputColumn::make('stock')
                    ->label('Stok')
                    ->type('number')
                    ->rules(['required', 'integer', 'min:0'])
                    ->extraInputAttributes(['step' => '1', 'min' => '0', 'style' => 'width:5rem'])
                    ->sortable(),
                ImageColumn::make('image_url')->label('Görsel'),
                TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('is_featured')
                    ->label('Vitrin')
                    ->sortable(),
                TextColumn::make('meta_title')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('satis_sayisi')
                    ->label('Satış')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('view_count')
                    ->label('Görüntülenme')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                // Hangi tükenmiş ürünü öncelikle tedarik etmek gerektiğini
                // söyleyen sütun: bekleyen sayısı yüksek olan ürün, hazır
                // müşterisi olan üründür.
                TextColumn::make('pending_stock_notifications_count')
                    ->label('Stok Bekleyen')
                    ->counts('pendingStockNotifications')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('stok_bekleyen_var')
                    ->label('Stok bildirimi bekleyen var')
                    ->query(fn (Builder $query) => $query->whereHas('pendingStockNotifications')),
            ])
            ->recordActions([
                ViewAction::make()->label('Detay'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(ProductExporter::class)
                        ->label('Seçilenleri Excel İndir')
                        ->icon('heroicon-m-arrow-down-tray'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
