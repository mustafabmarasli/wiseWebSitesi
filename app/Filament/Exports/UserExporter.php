<?php

namespace App\Filament\Exports;

use App\Enums\OrderStatus;
use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

/**
 * Kullanıcıları dışa aktarır.
 *
 * Parola hash'i ve remember_token bilinçli olarak DIŞARIDA bırakılmıştır —
 * bir tabloya asla girmemeliler.
 */
class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name')->label('Ad Soyad'),
            ExportColumn::make('email')->label('E-posta'),
            ExportColumn::make('is_admin')
                ->label('Rol')
                ->state(fn (User $record): string => $record->is_admin ? 'Yönetici' : 'Müşteri'),
            ExportColumn::make('email_verified_at')->label('E-posta Doğrulama'),
            ExportColumn::make('orders_count')
                ->label('Sipariş Sayısı')
                ->counts('orders'),
            ExportColumn::make('total_spent')
                ->label('Toplam Harcama')
                ->state(fn (User $record): string => number_format(
                    (float) $record->orders()->whereIn('status', OrderStatus::paidStatuses())->sum('total_amount'),
                    2, ',', '.'
                )),
            ExportColumn::make('addresses_count')
                ->label('Kayıtlı Adres')
                ->counts('addresses'),
            ExportColumn::make('created_at')->label('Kayıt Tarihi'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Kullanıcı dışa aktarımı tamamlandı: '
            . number_format($export->successful_rows, 0, ',', '.') . ' satır.';

        if ($failed = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failed, 0, ',', '.') . ' satır aktarılamadı.';
        }

        return $body;
    }
}
