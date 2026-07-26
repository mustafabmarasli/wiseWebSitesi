<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Support\OrderExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Sipariş No')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label('Müşteri')
                    ->weight('medium')
                    // Görünen alan hesaplanmış olduğu için arama gerçek kolonlara yapılır
                    ->searchable(['first_name', 'last_name'])
                    ->description(fn (Order $record): string => $record->email),

                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Telefon kopyalandı')
                    ->icon('heroicon-m-phone')
                    ->placeholder('—'),

                TextColumn::make('address')
                    ->label('Teslimat Adresi')
                    ->searchable(['address', 'city'])
                    ->wrap()
                    ->limit(60)
                    // Kısaltılan adresin tamamı üzerine gelince görünsün
                    ->tooltip(fn (Order $record): ?string => $record->address)
                    ->description(fn (Order $record): string => trim(
                        ($record->zip_code ? $record->zip_code . ' ' : '') . $record->city
                    )),

                TextColumn::make('is_corporate')
                    ->label('Fatura Tipi')
                    ->badge()
                    ->state(fn (Order $record): string => $record->is_corporate ? 'Ticari Fatura' : 'Bireysel')
                    ->color(fn (Order $record): string => $record->is_corporate ? 'info' : 'gray')
                    ->icon(fn (Order $record): string => $record->is_corporate
                        ? 'heroicon-m-building-office-2'
                        : 'heroicon-m-user')
                    ->description(fn (Order $record): ?string => $record->is_corporate
                        ? $record->company_name
                        : null),

                TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('city')
                    ->label('Şehir')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_amount')
                    ->label('Toplam Tutar')
                    ->money('TRY')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->color(fn (string $state): string => OrderStatus::colorFor($state))
                    ->formatStateUsing(fn (string $state): string => OrderStatus::labelFor($state)),
                TextColumn::make('payment_method')
                    ->label('Ödeme Yöntemi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipping_method')
                    ->label('Kargo Firması')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipping_cost')
                    ->label('Kargo Ücreti')
                    ->money('TRY')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('coupon_code')
                    ->label('Kupon Kodu')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('discount_amount')
                    ->label('İndirim Tutarı')
                    ->money('TRY')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Sipariş Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('estimated_delivery_at')
                    ->label('Tahmini Teslimat')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->multiple()
                    ->options(OrderStatus::options()),
                TernaryFilter::make('coupon_code')
                    ->label('Kupon kullanımı')
                    ->placeholder('Tümü')
                    ->trueLabel('Kupon kullanılanlar')
                    ->falseLabel('Kupon kullanılmayanlar')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('coupon_code'),
                        false: fn (Builder $q) => $q->whereNull('coupon_code'),
                    ),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->label('Başlangıç'),
                        DatePicker::make('until')->label('Bitiş'),
                    ])
                    ->query(fn (Builder $q, array $data): Builder => $q
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->headerActions([
                Action::make('export-all')
                    ->label('Tümünü Excel İndir')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('gray')
                    ->action(fn (HasTable $livewire): StreamedResponse => OrderExporter::download(
                        // Ekrandaki filtre ve aramayı da uygular
                        $livewire->getFilteredSortedTableQuery()
                    )),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('export-selected')
                        ->label('Seçilenleri Excel İndir')
                        ->icon('heroicon-m-arrow-down-tray')
                        ->color('gray')
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records): StreamedResponse => OrderExporter::download($records)),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
