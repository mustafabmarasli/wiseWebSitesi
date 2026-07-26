<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class MostViewedProducts extends TableWidget
{
    protected static ?int $sort = 8;

    protected static ?string $heading = 'En Çok Görüntülenen Ürünler';

    protected int|string|array $columnSpan = 'full';

    public function getPollingInterval(): ?string
    {
        return '600s';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with('category')
                    ->where('view_count', '>', 0)
                    ->orderByDesc('view_count')
            )
            ->emptyStateHeading('Henüz görüntülenme kaydı yok')
            ->emptyStateDescription('Ziyaretçiler ürün sayfalarını açtıkça burada listelenecek.')
            ->columns([
                TextColumn::make('name')
                    ->label('Ürün')
                    ->limit(45)
                    ->searchable()
                    ->url(fn (Product $record): string => route('filament.admin.resources.products.edit', $record)),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->toggleable(),
                TextColumn::make('view_count')
                    ->label('Görüntülenme')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('satis_sayisi')
                    ->label('Satış')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('conversion_rate')
                    ->label('Dönüşüm')
                    ->badge()
                    ->state(fn (Product $record): string => $record->conversion_rate === null
                        ? '—'
                        : $record->conversion_rate . '%')
                    ->color(fn (Product $record): string => match (true) {
                        $record->conversion_rate === null => 'gray',
                        $record->conversion_rate >= 5     => 'success',
                        $record->conversion_rate >= 1     => 'warning',
                        default                           => 'danger',
                    })
                    ->tooltip('Görüntülenme başına satış oranı'),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => $state <= 5 ? 'danger' : 'success'),
            ]);
    }
}
