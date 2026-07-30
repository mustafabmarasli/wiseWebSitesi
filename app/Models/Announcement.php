<?php

namespace App\Models;

use App\Models\Concerns\ResolvesImagePaths;
use Illuminate\Database\Eloquent\Model;

/**
 * Mağaza sayfası açıldığında beliren duyuru penceresi.
 *
 * Birden fazla duyuru kaydedilebilir ama bir sayfada **yalnızca biri**
 * gösterilir: o kanal için yayında olan, sıra numarası en küçük olan.
 * Aynı anda iki pencere açmak ziyaretçiyi iki kez kapatmaya zorlar.
 */
class Announcement extends Model
{
    use ResolvesImagePaths;

    protected $fillable = [
        'channel',
        'title',
        'body',
        'image_path',
        'image_alt',
        'layout',
        'bg_color',
        'text_color',
        'tone',
        'button_text',
        'button_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public const KANALLAR = [
        'both'        => 'Her iki mağaza',
        'electronics' => 'Yalnızca Elektronik',
        'health'      => 'Yalnızca Sağlık & Lens',
    ];

    public const YERLESIMLER = [
        'text'          => 'Sadece metin',
        'image_top'     => 'Görsel üstte, metin altta',
        'image_bottom'  => 'Metin üstte, görsel altta',
        'image_overlay' => 'Yazı görselin üzerinde',
    ];

    /** Görsel gösteren yerleşimler. */
    public const GORSELLI_YERLESIMLER = ['image_top', 'image_bottom', 'image_overlay'];

    public const TONLAR = [
        'info'     => 'Bilgi (mavi)',
        'warning'  => 'Uyarı (amber)',
        'campaign' => 'Kampanya (yeşil)',
        'none'     => 'Simge yok',
    ];

    /**
     * Bu kanalda gösterilecek duyurular, gösterim sırasına göre.
     *
     * Birden fazla duyuru varsa **sırayla** gösterilir: ziyaretçi birincisini
     * kapatınca ikincisi açılır. `both` kanalı her iki mağazada geçerlidir.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function queueForChannel(string $channel)
    {
        return static::query()
            ->where('is_active', true)
            ->whereIn('channel', ['both', $channel])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Kuyruğun ilk duyurusu. Panelde "hangisi ilk çıkıyor" göstergesi ve
     * tek duyuru bekleyen kodlar için.
     */
    public static function forChannel(string $channel): ?self
    {
        return static::queueForChannel($channel)->first();
    }

    /** Görsel gerçekten kullanılıyor mu? Yerleşim görsel istiyor ve görsel var mı. */
    public function usesImage(): bool
    {
        return in_array($this->layout, self::GORSELLI_YERLESIMLER, true)
            && filled($this->image_url);
    }

    /** Görsel metnin altında mı dursun? */
    public function imageBelowText(): bool
    {
        return $this->layout === 'image_bottom' && filled($this->image_url);
    }

    /**
     * Kartın arka plan rengi. Panelden seçilmemişse yerleşime göre varsayılan.
     *
     * "Yazı görselin üzerinde" yerleşiminde bu renk, görselin üstüne serilen
     * PERDE rengidir — açık zeminli görsellerde yazının okunmasını sağlar.
     */
    public function getBgColorValueAttribute(): string
    {
        if (filled($this->bg_color)) {
            return $this->bg_color;
        }

        return $this->isOverlay() ? '#020617' : '#FFFFFF';
    }

    /**
     * Yazı rengi. Seçilmemişse arka planın koyuluğuna göre otomatik:
     * koyu zeminde beyaz, açık zeminde koyu gri.
     */
    public function getTextColorValueAttribute(): string
    {
        if (filled($this->text_color)) {
            return $this->text_color;
        }

        return $this->isDarkBackground() ? '#FFFFFF' : '#0F172A';
    }

    /** Gövde metni rengi — başlıktan bir ton soluk. */
    public function getBodyColorValueAttribute(): string
    {
        if (filled($this->text_color)) {
            return $this->text_color;
        }

        return $this->isDarkBackground() ? '#E2E8F0' : '#475569';
    }

    /**
     * Arka plan koyu mu? Otomatik yazı rengi bunu kullanır.
     *
     * Basit parlaklık hesabı (ITU-R BT.601): göz yeşili kırmızıdan, kırmızıyı
     * maviden daha parlak algılıyor, bu yüzden kanallar eşit ağırlıklı değil.
     */
    public function isDarkBackground(): bool
    {
        $renk = ltrim($this->bg_color_value, '#');

        if (strlen($renk) === 3) {
            $renk = $renk[0] . $renk[0] . $renk[1] . $renk[1] . $renk[2] . $renk[2];
        }

        if (strlen($renk) !== 6 || ! ctype_xdigit($renk)) {
            return $this->isOverlay();
        }

        $r = hexdec(substr($renk, 0, 2));
        $g = hexdec(substr($renk, 2, 2));
        $b = hexdec(substr($renk, 4, 2));

        return (($r * 299) + ($g * 587) + ($b * 114)) / 1000 < 140;
    }

    /** Yazı görselin üzerine mi biniyor? */
    public function isOverlay(): bool
    {
        return $this->layout === 'image_overlay' && filled($this->image_url);
    }

    /**
     * Tona göre simge ve renkler.
     *
     * Renkler burada TAM değer olarak yazılır: duyuru penceresi Tailwind
     * yerine gömülü CSS kullanıyor (bkz. GELISTIRICI-NOTLARI madde 1'in
     * mantığı — birleştirilmiş sınıf adları CDN taramasında görünmez).
     *
     * @return array{ikon: ?string, zemin: string, renk: string}|null
     */
    public function getToneStyleAttribute(): ?array
    {
        return match ($this->tone) {
            'info' => [
                'zemin' => '#EFF6FF', 'renk' => '#2563EB',
                'ikon'  => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            'warning' => [
                'zemin' => '#FFFBEB', 'renk' => '#F59E0B',
                'ikon'  => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            ],
            'campaign' => [
                'zemin' => '#ECFDF5', 'renk' => '#059669',
                'ikon'  => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
            ],
            default => null,
        };
    }

    public function getChannelLabelAttribute(): string
    {
        return self::KANALLAR[$this->channel] ?? $this->channel;
    }
}
