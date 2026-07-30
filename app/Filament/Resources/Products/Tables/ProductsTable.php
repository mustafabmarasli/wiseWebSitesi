<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Exports\ProductExporter;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Ürün Adı')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand')
                    ->label('Marka')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gtin')
                    ->label('Barkod')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label('Puan')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_featured')
                    ->label('Vitrin')
                    ->sortable(),
                TextColumn::make('meta_title')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Eklenme')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Güncelleme')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('satis_sayisi')
                    ->label('Satış')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('view_count')
                    ->label('Görüntülenme')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('brand')
                    ->label('Marka')
                    // Sabit liste yerine veritabanındaki gerçek markalardan
                    // üretilir; yeni marka eklenince filtreye elle eklemek
                    // gerekmez.
                    ->options(fn () => Product::query()
                        ->whereNotNull('brand')
                        ->where('brand', '!=', '')
                        ->distinct()
                        ->orderBy('brand')
                        ->pluck('brand', 'brand')
                        ->all())
                    ->searchable(),

                // Stok durumu: tükenmiş / az stok / stokta. Panel her gün
                // "hangi ürünler tükenmiş" ve "hangileri azalıyor" sorusuna
                // cevap vermek için açılıyor; tek tek stok sütununu taramak
                // yerine tek tıkla filtrelenebilsin.
                SelectFilter::make('stok_durumu')
                    ->label('Stok Durumu')
                    ->options([
                        'tukendi'  => 'Tükendi (0)',
                        'az_stok'  => 'Az Stok (1-9)',
                        'stokta'   => 'Stokta (10+)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'tukendi' => $query->where('stock', '<=', 0),
                            'az_stok' => $query->whereBetween('stock', [1, 9]),
                            'stokta'  => $query->where('stock', '>=', 10),
                            default   => $query,
                        };
                    }),

                TernaryFilter::make('is_featured')
                    ->label('Vitrin')
                    ->trueLabel('Yalnızca vitrindekiler')
                    ->falseLabel('Yalnızca vitrinde olmayanlar'),

                Filter::make('indirimli')
                    ->label('İndirimli (eski fiyatı olan)')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('eski_fiyat')
                        ->whereColumn('eski_fiyat', '>', 'price')),

                // Fiyat aralığı: iki alanlı özel filtre. Panelde "500-1000 TL
                // arası ürünleri göster" gibi sorular sık soruluyor, tek
                // sütun sıralaması bu soruyu cevaplamıyor.
                Filter::make('fiyat_araligi')
                    ->label('Fiyat Aralığı')
                    ->schema([
                        TextInput::make('fiyat_min')->label('Min ₺')->numeric()->minValue(0),
                        TextInput::make('fiyat_max')->label('Max ₺')->numeric()->minValue(0),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['fiyat_min'] ?? null, fn ($q, $v) => $q->where('price', '>=', $v))
                            ->when($data['fiyat_max'] ?? null, fn ($q, $v) => $q->where('price', '<=', $v));
                    })
                    ->indicateUsing(function (array $data): array {
                        $göstergeler = [];

                        if ($data['fiyat_min'] ?? null) {
                            $göstergeler[] = 'Min: ' . number_format((float) $data['fiyat_min'], 2, ',', '.') . ' ₺';
                        }

                        if ($data['fiyat_max'] ?? null) {
                            $göstergeler[] = 'Max: ' . number_format((float) $data['fiyat_max'], 2, ',', '.') . ' ₺';
                        }

                        return $göstergeler;
                    }),

                Filter::make('stok_bekleyen_var')
                    ->label('Stok bildirimi bekleyen var')
                    ->query(fn (Builder $query) => $query->whereHas('pendingStockNotifications')),
            ])
            ->filtersFormColumns(2)
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
