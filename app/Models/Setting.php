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
        'announcement_enabled',
        'announcement_title',
        'announcement_text',
        'consulting_enabled',
        'bank_transfer_enabled',
        'bank_transfer_discount_percent',
        'bank_account_holder',
        'bank_name',
        'bank_iban',
        'bank_transfer_note',
        'card_payment_enabled',
    ];

    protected $casts = [
        'standard_shipping_cost'  => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'announcement_enabled'    => 'boolean',
        'consulting_enabled'      => 'boolean',
        'bank_transfer_enabled'   => 'boolean',
        'card_payment_enabled'    => 'boolean',
        'bank_transfer_discount_percent' => 'decimal:2',
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
     * Duyuru gösterilecek mi? Metin boşsa aktif olsa bile gösterilmez.
     */
    public function showsAnnouncement(): bool
    {
        return $this->announcement_enabled && filled($this->announcement_text);
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

    /**
     * Havale/EFT seçeneği müşteriye sunulabilir mi?
     * Banka bilgileri eksikse açık olsa bile sunulmaz — aksi hâlde müşteri
     * siparişi tamamlayıp ödeme yapacak hesabı göremezdi.
     */
    public function offersBankTransfer(): bool
    {
        return $this->bank_transfer_enabled
            && filled($this->bank_iban)
            && filled($this->bank_account_holder);
    }

    /** Kredi kartı seçeneği müşteriye sunulabilir mi? */
    public function offersCardPayment(): bool
    {
        return (bool) $this->card_payment_enabled;
    }

    /**
     * Havale/EFT indirimi tutarı.
     *
     * @param  float  $subtotal  Kupon indirimi düşülmüş, kargo hariç tutar.
     */
    public function bankTransferDiscountFor(float $subtotal): float
    {
        $percent = (float) $this->bank_transfer_discount_percent;

        if ($percent <= 0 || $subtotal <= 0) {
            return 0.0;
        }

        return round(min($subtotal * $percent / 100, $subtotal), 2);
    }
}
