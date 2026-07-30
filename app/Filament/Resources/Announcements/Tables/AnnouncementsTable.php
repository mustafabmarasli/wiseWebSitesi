<?php

namespace App\Filament\Resources\Announcements\Tables;

use App\Models\Announcement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Görsel')
                    ->height(40),

                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->limit(45),

                TextColumn::make('channel')
                    ->label('Nerede')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Announcement::KANALLAR[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'health'      => 'success',
                        'electronics' => 'info',
                        default       => 'gray',
                    }),

                TextColumn::make('layout')
                    ->label('Yerleşim')
                    ->formatStateUsing(fn (string $state): string => Announcement::YERLESIMLER[$state] ?? $state)
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Sıra')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Yayında'),

                // Duyurular sırayla gösterildiği için hangisinin kaçıncı
                // çıktığını söylemek gerekiyor.
                TextColumn::make('gosterim')
                    ->label('Durum')
                    ->state(function (Announcement $kayit): string {
                        if (! $kayit->is_active) {
                            return 'Taslak';
                        }

                        $kanal = $kayit->channel === 'both' ? 'electronics' : $kayit->channel;
                        $sira  = Announcement::queueForChannel($kanal)
                            ->search(fn ($d) => $d->is($kayit));

                        return $sira === 0 ? 'İlk açılan' : ($sira + 1) . '. sırada';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'İlk açılan' => 'success',
                        'Taslak'     => 'gray',
                        default      => 'warning',
                    }),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label('Nerede')
                    ->options(Announcement::KANALLAR),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Henüz duyuru yok')
            ->emptyStateDescription('Mağaza sayfası açıldığında beliren pencere. Görsel, biçimli metin ve bağlantı butonu ekleyebilirsiniz.');
    }
}
