<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kupon Kodu')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Kupon Türü')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'percent' => 'Yüzde (%)',
                        'fixed' => 'Sabit Tutar (₺)',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'percent' => 'info',
                        'fixed' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('value')
                    ->label('Kupon Değeri')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('usage')
                    ->label('Kullanım (Kullanılan / Limit)')
                    ->state(fn ($record) => $record->used_count . ' / ' . ($record->max_uses ?? 'Sınırsız')),
                IconColumn::make('active')
                    ->label('Aktif mi?')
                    ->boolean(),
                TextColumn::make('expires_at')
                    ->label('Son Kullanma Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
