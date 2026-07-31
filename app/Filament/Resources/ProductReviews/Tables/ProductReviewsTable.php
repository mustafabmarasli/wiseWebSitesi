<?php

namespace App\Filament\Resources\ProductReviews\Tables;

use App\Models\ProductReview;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ProductReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Ürün')
                    ->searchable()
                    ->limit(40)
                    ->url(fn (ProductReview $r): ?string => $r->product
                        ? route('product.detail', $r->product->slug)
                        : null)
                    ->openUrlInNewTab(),

                TextColumn::make('user.name')
                    ->label('Müşteri')
                    ->searchable()
                    ->description(fn (ProductReview $r): ?string => $r->order?->display_number),

                TextColumn::make('rating')
                    ->label('Puan')
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state) . str_repeat('☆', 5 - $state))
                    ->color('warning'),

                TextColumn::make('comment')
                    ->label('Yorum')
                    ->wrap()
                    ->limit(120),

                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ProductReview::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(ProductReview::STATUSES),
            ])
            ->recordActions([
                Action::make('onayla')
                    ->label('Onayla')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (ProductReview $r): bool => $r->status !== 'approved')
                    ->action(function (ProductReview $r) {
                        $r->approve();
                        Notification::make()->title('Yorum yayına alındı.')->success()->send();
                    }),

                Action::make('reddet')
                    ->label('Reddet')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Reddedilen yorum sitede hiçbir zaman görünmez ama kayıt silinmez.')
                    ->visible(fn (ProductReview $r): bool => $r->status !== 'rejected')
                    ->action(function (ProductReview $r) {
                        $r->reject();
                        Notification::make()->title('Yorum reddedildi.')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('topluOnayla')
                        ->label('Seçilenleri Onayla')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->approve())
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Henüz yorum yok')
            ->emptyStateDescription('Müşteriler yalnızca teslim aldıkları ürünlere yorum yazabilir; yorumlar burada onay bekler.');
    }
}
