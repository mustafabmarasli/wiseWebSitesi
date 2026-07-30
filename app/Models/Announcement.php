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
        'image_overlay' => 'Yazı görselin üzerinde',
    ];

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
        return in_array($this->layout, ['image_top', 'image_overlay'], true)
            && filled($this->image_url);
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
