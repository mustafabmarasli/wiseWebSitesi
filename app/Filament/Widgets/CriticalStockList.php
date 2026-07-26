<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Support\Csv;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CriticalStockList extends TableWidget
{
    protected static ?int $sort = 11;

    protected static ?string $heading = 'Stok Kritik Ürünler (Stok ≤ 5)';

    protected int | string | array $columnSpan = 'full';

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
                    ->where('stock', '<=', 5)
                    ->orderBy('stock')
            )
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label('Ürün Adı')
                    ->limit(50)
                    ->searchable()
                    ->url(fn (Product $record): string => route('filament.admin.resources.products.edit', $record)),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->toggleable(),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => $state <= 0 ? 'danger' : ($state <= 3 ? 'warning' : 'info'))
                    ->formatStateUsing(fn (int $state): string => $state <= 0 ? '🛑 ' . $state : ($state <= 3 ? '⚠️ ' . $state : (string) $state)),
                TextColumn::make('price')
                    ->label('Fiyat')
                    ->money('TRY')
                    ->toggleable(),
                TextColumn::make('satis_sayisi')
                    ->label('Satış Sayısı')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('export-csv')
                    ->label('CSV İndir')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->action(fn (): StreamedResponse => $this->exportToCsv()),
            ])
            ->recordActions([
                Action::make('stok-guncelle')
                    ->label('Stok Güncelle')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Product $record): string => route('filament.admin.resources.products.edit', $record)),
            ]);
    }

    public function exportToCsv(): StreamedResponse
    {
        $rows = [['ID', 'Ürün Adı', 'Kategori', 'Stok', 'Fiyat (TL)', 'Satış Sayısı']];

        Product::with('category')
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->chunk(500, function ($products) use (&$rows) {
                foreach ($products as $p) {
                    $rows[] = [$p->id, $p->name, $p->category?->name, $p->stock, $p->price, $p->satis_sayisi];
                }
            });

        return Csv::download('kritik_stok_' . now()->format('Y-m-d_H-i-s') . '.csv', $rows);
    }
}
