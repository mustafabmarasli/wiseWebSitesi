<?php

namespace App\Filament\Exports;

use App\Models\MarketingConsent;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

/**
 * Ticari elektronik ileti onaylarını dışa aktarır.
 *
 * Sütunlar İYS'nin (İleti Yönetim Sistemi) beklediği alanlara karşılık
 * gelecek şekilde seçildi: alıcı, kanal (İZİN TİPİ), onay durumu, onay
 * tarihi, kaynak ve IP. Uyuşmazlıkta ispat yükü göndericide olduğu için
 * tarih, kaynak ve IP kolonları çıkarılmamalı.
 */
class MarketingConsentExporter extends Exporter
{
    protected static ?string $model = MarketingConsent::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('contact')
                ->label('Alıcı')
                ->state(fn (MarketingConsent $record): string => $record->contact),

            ExportColumn::make('channel')
                ->label('İzin Tipi (İYS)')
                ->state(fn (MarketingConsent $record): string => MarketingConsent::IYS_KARSILIKLARI[$record->channel] ?? $record->channel),

            ExportColumn::make('status')
                ->label('Onay Durumu')
                ->state(fn (MarketingConsent $record): string => $record->isGranted() ? 'ONAY' : 'RET'),

            ExportColumn::make('consented_at')->label('Onay Tarihi'),
            ExportColumn::make('revoked_at')->label('Ret Tarihi'),

            ExportColumn::make('source')
                ->label('Onay Kaynağı')
                ->state(fn (MarketingConsent $record): string => MarketingConsent::KAYNAKLAR[$record->source] ?? $record->source),

            ExportColumn::make('ip_address')->label('IP Adresi'),
            ExportColumn::make('synced_to_iys_at')->label('İYS Yükleme Tarihi'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Onay listesi hazır: ' . number_format($export->successful_rows) . ' satır. '
            . 'İYS yüklemesinden sonra kayıtları "İYS\'ye yüklendi" olarak işaretlemeyi unutmayın.';
    }
}
