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
        // Duyuru alanları buradan çıktı: artık `announcements` tablosunda,
        // çoklu ve görselli. Bkz. App\Models\Announcement.
        'consulting_enabled',
        'new_customer_telegram_enabled',
        'marketing_sending_enabled',
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
        'consulting_enabled'      => 'boolean',
        'new_customer_telegram_enabled' => 'boolean',
        'marketing_sending_enabled'     => 'boolean',
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
     * Vitrinde gösterilecek kargo rozetinin metni.
     *
     * Ürün sayfası, anasayfa şeridi ve kategori kartları buradan beslenir;
     * hiçbiri "kargo bedava"yı KENDİ BAŞINA yazmamalı. Eşik panelden
     * değiştirilebiliyor ve müşteriye ödeme adımında farklı bir tutar
     * çıkması en pahalı sepet terk sebebidir.
     *
     * @param  float|null  $subtotal  Bilinen tutar — ürün sayfasında o ürünün
     *   fiyatı, sepette ara toplam. null geçilirse tutardan bağımsız,
     *   koşulu açıkça yazan genel ifade döner.
     * @return array{free: bool, title: string, detail: string}
     *   `free`: bu bağlamda kargo gerçekten ücretsiz mi (rozetin yeşil
     *   olup olmayacağını bu belirler).
     */
    public function shippingNotice(?float $subtotal = null): array
    {
        $cost = (float) $this->standard_shipping_cost;

        // Kargo ücreti hiç tanımlanmamış: her siparişte gerçekten ücretsiz.
        if ($cost <= 0) {
            return ['free' => true, 'title' => 'Ücretsiz Kargo', 'detail' => 'Tüm siparişlerde'];
        }

        $threshold = $this->free_shipping_threshold;

        // Kampanya kapalı: ücreti gizlemek yerine açıkça yazıyoruz.
        if ($threshold === null) {
            return [
                'free'   => false,
                'title'  => 'Standart Kargo',
                'detail' => number_format($cost, 2, ',', '.') . ' TL',
            ];
        }

        if ($subtotal !== null && $subtotal >= (float) $threshold) {
            return ['free' => true, 'title' => 'Ücretsiz Kargo', 'detail' => 'Bu ürün için geçerli'];
        }

        return [
            'free'   => false,
            'title'  => 'Ücretsiz Kargo',
            'detail' => number_format((float) $threshold, 2, ',', '.') . ' TL ve üzeri siparişlerde',
        ];
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

    /**
     * Yeni müşteri kaydında Telegram bildirimi gönderilsin mi?
     *
     * Panelden açılıp kapatılır; Telegram hiç yapılandırılmamışsa ayar açık
     * olsa da `TelegramNotifier` sessizce devre dışı kalır.
     */
    public function notifiesNewCustomer(): bool
    {
        return (bool) $this->new_customer_telegram_enabled;
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
