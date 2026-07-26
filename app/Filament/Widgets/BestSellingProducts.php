<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class BestSellingProducts extends TableWidget
{
    protected static ?string $heading = 'En Çok Satan Ürünler (Top 5)';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()->orderBy('satis_sayisi', 'desc')->limit(5)
            )
            ->paginated(false)
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Görsel')
                    ->circular(),
                TextColumn::make('name')
                    ->label('Ürün Adı')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Kategori'),
                TextColumn::make('price')
                    ->label('Fiyat')
                    ->money('TRY'),
                TextColumn::make('satis_sayisi')
                    ->label('Toplam Satış Adedi')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock')
                    ->label('Kalan Stok')
                    ->numeric()
                    ->color(fn (int $state): string => $state <= 5 ? 'danger' : 'success')
                    ->sortable(),
            ]);
    }
}
