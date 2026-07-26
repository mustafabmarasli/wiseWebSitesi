<?php

namespace App\Enums;

/**
 * Sipariş durumları.
 *
 * Durum metinleri ve renkleri daha önce 6+ dosyada elle tekrarlanıyordu;
 * yeni bir durum eklemek hepsini tek tek güncellemeyi gerektiriyordu.
 * Tek kaynak burasıdır.
 */
enum OrderStatus: string
{
    case Pending   = 'pending';
    case Paid      = 'paid';
    case Shipped   = 'shipped';
    case Delivered = 'delivered';
    case Failed    = 'failed';
    case Review    = 'review';
    case Refunded  = 'refunded';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Ödeme Bekliyor',
            self::Paid      => 'Ödendi / Hazırlanıyor',
            self::Shipped   => 'Kargoya Verildi',
            self::Delivered => 'Teslim Edildi',
            self::Failed    => 'Ödeme Başarısız',
            self::Review    => 'İnceleme Gerekiyor',
            self::Refunded  => 'İade Edildi',
            self::Cancelled => 'İptal Edildi',
        };
    }

    /** Filament rozet rengi. */
    public function color(): string
    {
        return match ($this) {
            self::Paid, self::Delivered => 'success',
            self::Shipped               => 'info',
            self::Pending               => 'warning',
            self::Refunded              => 'gray',
            default                     => 'danger',
        };
    }

    /** Ödemesi alınmış, ciroya sayılan durumlar. */
    public static function paidStatuses(): array
    {
        return [self::Paid->value, self::Shipped->value, self::Delivered->value];
    }

    /**
     * Filament select/filter için ['pending' => 'Ödeme Bekliyor', ...]
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }

    /**
     * Bilinmeyen bir değer gelirse metnin kendisini döndür (eski kayıtlar için).
     */
    public static function labelFor(?string $value): string
    {
        return self::tryFrom((string) $value)?->label() ?? (string) $value;
    }

    public static function colorFor(?string $value): string
    {
        return self::tryFrom((string) $value)?->color() ?? 'gray';
    }
}
