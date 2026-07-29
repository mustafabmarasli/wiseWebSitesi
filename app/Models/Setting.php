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
        'identity_required_threshold',
    ];

    protected $casts = [
        'standard_shipping_cost'  => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'announcement_enabled'    => 'boolean',
        'consulting_enabled'      => 'boolean',
        'bank_transfer_enabled'   => 'boolean',
        'card_payment_enabled'    => 'boolean',
        'bank_transfer_discount_percent' => 'decimal:2',
        'identity_required_threshold'    => 'decimal:2',
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

    /**
     * Bu sipariş için TC Kimlik No zorunlu mu?
     *
     * Vergi mükellefi olmayan nihai tüketiciye kesilen faturada TC Kimlik No
     * zorunlu değildir; yalnızca tutar fatura düzenleme haddini aştığında
     * e-Arşiv faturaya yazılması gerekir. Ticari faturada ise her hâlükârda
     * VKN/TCKN gerekir. Gerekmiyorken toplamak KVKK'nın veri minimizasyonu
     * ilkesiyle çelişir ve ödeme adımında terk sebebidir.
     *
     * @param  float  $netTotal   Ödenecek nihai tutar.
     * @param  bool   $corporate  Ticari fatura isteniyor mu?
     * @param  bool   $cardPayment  Kartla ödeme (iyzico bu alanı zorunlu tutar).
     */
    public function requiresIdentityNumber(float $netTotal, bool $corporate = false, bool $cardPayment = false): bool
    {
        if ($corporate || $cardPayment) {
            return true;
        }

        $threshold = (float) $this->identity_required_threshold;

        // Eşik 0 ise "her siparişte zorunlu" anlamına gelir.
        if ($threshold <= 0) {
            return true;
        }

        return $netTotal >= $threshold;
    }
}
