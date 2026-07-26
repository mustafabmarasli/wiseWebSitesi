<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tek satırlık site ayarları.
 */
class Setting extends Model
{
    protected $fillable = [
        'standard_shipping_cost',
        'free_shipping_threshold',
    ];

    protected $casts = [
        'standard_shipping_cost'  => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
    ];

    /**
     * Ayar satırını döndürür; yoksa varsayılanlarla oluşturur.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'standard_shipping_cost'  => 0,
            'free_shipping_threshold' => null,
        ]);
    }

    /**
     * Verilen sepet tutarı için ödenecek kargo ücretini hesaplar.
     *
     * @param  float  $subtotal  İndirim uygulandıktan sonraki sepet tutarı.
     */
    public function shippingCostFor(float $subtotal): float
    {
        $cost = (float) $this->standard_shipping_cost;

        if ($cost <= 0) {
            return 0.0;
        }

        $threshold = $this->free_shipping_threshold;

        if ($threshold !== null && $subtotal >= (float) $threshold) {
            return 0.0;
        }

        return $cost;
    }

    /**
     * Ücretsiz kargoya ulaşmak için gereken kalan tutar.
     * Kampanya kapalıysa veya zaten hak kazanıldıysa null döner.
     */
    public function remainingForFreeShipping(float $subtotal): ?float
    {
        if ((float) $this->standard_shipping_cost <= 0) {
            return null;
        }

        $threshold = $this->free_shipping_threshold;

        if ($threshold === null || $subtotal >= (float) $threshold) {
            return null;
        }

        return round((float) $threshold - $subtotal, 2);
    }
}
