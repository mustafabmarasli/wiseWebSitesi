<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Kapak')
                    ->height(40),

                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->limit(50)
                    ->description(fn (Post $r): ?string => $r->slug),

                TextColumn::make('channel')
                    ->label('Bölüm')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Post::KANALLAR[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'health'      => 'success',
                        'electronics' => 'info',
                        default       => 'gray',
                    }),

                TextColumn::make('published_at')
                    ->label('Yayın Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    // İleri tarihli yazı "yayında" görünüp sitede olmadığı için
                    // kafa karıştırıyordu; durumu burada açıkça yazıyoruz.
                    ->description(fn (Post $r): ?string => $r->is_published && $r->published_at?->isFuture()
                        ? 'Henüz yayınlanmadı'
                        : null),

                ToggleColumn::make('is_published')
                    ->label('Yayında'),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label('Bölüm')
                    ->options(Post::KANALLAR),

                SelectFilter::make('is_published')
                    ->label('Durum')
                    ->options([1 => 'Yayında', 0 => 'Taslak']),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Henüz yazı yok')
            ->emptyStateDescription('Rehber yazıları arama trafiği getirir; yazının içinden ürüne bağlantı vermeyi unutmayın.');
    }
}
