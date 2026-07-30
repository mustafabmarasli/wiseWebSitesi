<?php

namespace App\Filament\Resources\MarketingConsents\Tables;

use App\Filament\Exports\MarketingConsentExporter;
use App\Models\MarketingConsent;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class MarketingConsentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('consented_at', 'desc')
            ->columns([
                TextColumn::make('contact')
                    ->label('Alıcı')
                    ->state(fn (MarketingConsent $r): string => $r->contact)
                    ->searchable(['email', 'phone'])
                    ->description(fn (MarketingConsent $r): ?string => $r->email && $r->phone ? $r->phone : null),

                TextColumn::make('channel')
                    ->label('Kanal')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => MarketingConsent::KANALLAR[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'email' => 'info',
                        'sms'   => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'granted' ? 'Onaylı' : 'Çıkış yapıldı')
                    ->color(fn (string $state): string => $state === 'granted' ? 'success' : 'danger'),

                TextColumn::make('source')
                    ->label('Kaynak')
                    ->formatStateUsing(fn (string $state): string => MarketingConsent::KAYNAKLAR[$state] ?? $state)
                    ->toggleable(),

                TextColumn::make('consented_at')
                    ->label('Onay Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('revoked_at')
                    ->label('Çıkış Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),

                // Yüklenmemiş onaya gönderim yapılamaz; bu sütun neyin
                // beklediğini gösterir.
                TextColumn::make('synced_to_iys_at')
                    ->label('İYS')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => $state ? 'Yüklendi' : 'Bekliyor')
                    ->color(fn ($state): string => $state ? 'success' : 'warning'),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label('Kanal')
                    ->options(MarketingConsent::KANALLAR),

                SelectFilter::make('status')
                    ->label('Durum')
                    ->options(['granted' => 'Onaylı', 'revoked' => 'Çıkış yapıldı']),

                SelectFilter::make('synced_to_iys_at')
                    ->label('İYS Durumu')
                    ->options(['bekliyor' => 'Yüklenmeyi bekliyor', 'yuklendi' => 'Yüklendi'])
                    ->query(fn ($query, array $data) => match ($data['value'] ?? null) {
                        'bekliyor' => $query->whereNull('synced_to_iys_at'),
                        'yuklendi' => $query->whereNotNull('synced_to_iys_at'),
                        default    => $query,
                    }),
            ])
            ->recordActions([
                // Müşteri talebiyle (telefonla, e-postayla) çıkış isteyenler
                // için. Kayıt silinmez, "ret" olarak işaretlenir.
                Action::make('cikar')
                    ->label('Onayı Geri Çek')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Onay geri çekilsin mi?')
                    ->modalDescription('Kayıt silinmez, "çıkış yapıldı" olarak işaretlenir. İspat için tarihçe korunur.')
                    ->visible(fn (MarketingConsent $r): bool => $r->isGranted())
                    ->action(fn (MarketingConsent $r) => $r->revoke('admin')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(MarketingConsentExporter::class)
                        ->label('Seçilenleri Excel İndir (İYS)')
                        ->icon('heroicon-m-arrow-down-tray'),

                    BulkAction::make('iysYuklendi')
                        ->label('İYS\'ye Yüklendi Olarak İşaretle')
                        ->icon('heroicon-m-check-badge')
                        ->requiresConfirmation()
                        ->modalDescription('Onayları İYS\'ye yükledikten SONRA işaretleyin. Bu düğme İYS\'ye yükleme yapmaz, yalnızca kayıt tutar.')
                        ->action(fn (Collection $records) => $records->each->update(['synced_to_iys_at' => now()])),
                ]),
            ])
            ->emptyStateHeading('Henüz onay yok')
            ->emptyStateDescription('Müşteriler üyelik veya ödeme adımındaki isteğe bağlı kutuları işaretledikçe burada listelenir.');
    }
}
