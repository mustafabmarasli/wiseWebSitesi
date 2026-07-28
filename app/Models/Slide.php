<?php

namespace App\Models;

use App\Models\Concerns\ResolvesImagePaths;
use Illuminate\Database\Eloquent\Model;

/**
 * Anasayfa (Elektronik / Sağlık) üst slider görselleri.
 *
 * Önceden `home.blade.php` içine gömülüydü; her değişiklik kod düzenlemesi ve
 * yayın gerektiriyordu. Artık panelden yönetilir.
 */
class Slide extends Model
{
    use ResolvesImagePaths;

    protected $fillable = [
        'channel',
        'image_path',
        'image_alt',
        'badge',
        'badge_color',
        'title',
        'subtitle',
        'primary_text',
        'primary_url',
        'secondary_text',
        'secondary_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Etiket renkleri — panelde seçilir, görünümde Tailwind sınıfına çevrilir. */
    public const RENKLER = [
        'trendyol' => 'Lacivert (marka)',
        'emerald'  => 'Yeşil',
        'amber'    => 'Turuncu',
        'blue'     => 'Mavi',
        'rose'     => 'Kırmızı',
        'slate'    => 'Gri',
    ];

    public const KANALLAR = [
        'electronics' => 'Elektronik',
        'health'      => 'Sağlık & Lens',
    ];

    /**
     * Etiketin Tailwind arka plan sınıfı.
     *
     * Sınıf adları burada TAM olarak yazılır; Tailwind CDN sayfayı tarayarak
     * sınıf üretiyor, `bg-{$renk}-500` gibi birleştirilmiş adları göremez.
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->badge_color) {
            'emerald' => 'bg-emerald-500',
            'amber'   => 'bg-amber-500',
            'blue'    => 'bg-blue-500',
            'rose'    => 'bg-rose-500',
            'slate'   => 'bg-slate-500',
            default   => 'bg-trendyol',
        };
    }

    /** Belirli bir kanalın yayındaki slaytları, sıraya göre. */
    public static function forChannel(string $channel)
    {
        return static::query()
            ->where('channel', $channel)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
