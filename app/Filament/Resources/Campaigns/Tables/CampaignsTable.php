<?php

namespace App\Filament\Resources\Campaigns\Tables;

use App\Models\Campaign;
use App\Services\CampaignSender;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Kampanya')
                    ->searchable()
                    ->limit(45),

                TextColumn::make('channel')
                    ->label('Kanal')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Campaign::KANALLAR[$state] ?? $state)
                    ->color(fn (string $state): string => $state === 'sms' ? 'success' : 'info'),

                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Campaign::DURUMLAR[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'sent'    => 'success',
                        'failed'  => 'danger',
                        'sending', 'queued' => 'warning',
                        default   => 'gray',
                    }),

                TextColumn::make('audience')
                    ->label('Hedef')
                    ->state(fn (Campaign $r): string => $r->audienceCount() . ' onaylı'),

                TextColumn::make('sent_count')
                    ->label('Gönderilen')
                    ->badge()
                    ->color('success'),

                TextColumn::make('failed_count')
                    ->label('Hata')
                    ->badge()
                    ->color(fn ($state): string => $state > 0 ? 'danger' : 'gray'),

                TextColumn::make('completed_at')
                    ->label('Bitiş')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('channel')->label('Kanal')->options(Campaign::KANALLAR),
                SelectFilter::make('status')->label('Durum')->options(Campaign::DURUMLAR),
            ])
            ->recordActions([
                EditAction::make()
                    // Gönderilmiş kampanya değiştirilemez: gönderilen metnin
                    // kaydı bozulmamalı.
                    ->visible(fn (Campaign $r): bool => $r->isDraft()),

                // Önce kendine gönder, sonra listeye. Yazım hatasını
                // 500 kişiye gönderdikten sonra fark etmek pahalı.
                Action::make('test')
                    ->label('Deneme Gönder')
                    ->icon('heroicon-m-paper-airplane')
                    ->color('gray')
                    ->schema([
                        TextInput::make('hedef')
                            ->label('Deneme adresi / numarası')
                            ->required()
                            ->helperText('Kendi e-postanı veya telefonunu yaz. Bu gönderim müşteri listesine dokunmaz.'),
                    ])
                    ->action(function (Campaign $record, array $data) {
                        $sonuc = (new CampaignSender())->sendTest($record, $data['hedef']);

                        Notification::make()
                            ->title($sonuc['ok'] ? 'Deneme gönderildi' : 'Deneme gönderilemedi')
                            ->body($sonuc['message'])
                            ->{$sonuc['ok'] ? 'success' : 'danger'}()
                            ->persistent()
                            ->send();
                    }),

                Action::make('gonder')
                    ->label('Gönderime Al')
                    ->icon('heroicon-m-rocket-launch')
                    ->color('danger')
                    ->visible(fn (Campaign $r): bool => $r->isDraft())
                    ->requiresConfirmation()
                    ->modalHeading('Kampanya gönderilsin mi?')
                    ->modalDescription(fn (Campaign $r): string =>
                        $r->audienceCount() . ' onaylı alıcıya gönderilecek. Bu işlem geri alınamaz. '
                        . 'Gönderim, sunucuda "php artisan campaigns:send" komutu çalıştığında başlar.')
                    ->modalSubmitActionLabel('Evet, gönderime al')
                    ->action(function (Campaign $record) {
                        $engel = (new CampaignSender())->blocker($record);

                        if ($engel) {
                            Notification::make()
                                ->title('Gönderim yapılamaz')
                                ->body($engel)
                                ->danger()
                                ->persistent()
                                ->send();

                            return;
                        }

                        $record->update(['status' => 'queued']);

                        Notification::make()
                            ->title('Kampanya sıraya alındı')
                            ->body('Sunucuda "php artisan campaigns:send" çalıştığında gönderim başlayacak.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Henüz kampanya yok')
            ->emptyStateDescription('Gönderim yalnızca onay vermiş kişilere yapılır. Onay listesi: Ticari İleti Onayları.');
    }
}
