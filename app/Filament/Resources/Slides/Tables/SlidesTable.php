<?php

namespace App\Filament\Resources\Slides\Tables;

use App\Models\Slide;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Görsel')
                    ->height(50),

                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->limit(40)
                    ->description(fn (Slide $r): ?string => $r->badge),

                TextColumn::make('channel')
                    ->label('Sayfa')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Slide::KANALLAR[$state] ?? $state)
                    ->color(fn (string $state): string => $state === 'health' ? 'success' : 'info'),

                TextColumn::make('sort_order')
                    ->label('Sıra')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Yayında'),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label('Sayfa')
                    ->options(Slide::KANALLAR),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Henüz slayt yok')
            ->emptyStateDescription('Anasayfadaki büyük görselleri buradan ekleyip sıralayabilirsiniz.');
    }
}
