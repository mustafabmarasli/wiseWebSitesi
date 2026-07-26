<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentOrders extends TableWidget
{
    protected static ?string $heading = 'Son Gelen Siparişler';

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()->limit(5)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('id')
                    ->label('Sipariş No'),
                TextColumn::make('first_name')
                    ->label('Müşteri Adı'),
                TextColumn::make('last_name')
                    ->label('Müşteri Soyadı'),
                TextColumn::make('total_amount')
                    ->label('Toplam Tutar')
                    ->money('TRY'),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'shipped' => 'info',
                        'delivered' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Ödeme Bekliyor',
                        'paid' => 'Ödendi',
                        'shipped' => 'Kargoya Verildi',
                        'delivered' => 'Teslim Edildi',
                        'failed' => 'Başarısız',
                        'refunded' => 'İade Edildi',
                        'cancelled' => 'İptal Edildi',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i'),
            ]);
    }
}
