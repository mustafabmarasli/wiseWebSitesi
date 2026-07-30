<?php

namespace App\Models;

use App\Models\Concerns\ResolvesImagePaths;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Blog / rehber yazısı.
 *
 * Yayında sayılmak için İKİ koşul birden gerekir: `is_published` açık VE
 * `published_at` geçmişte. Böylece taslak açıp ileri tarihe kurmak mümkün;
 * tek bir "yayında" bayrağı olsaydı zamanlanmış yayın yapılamazdı.
 */
class Post extends Model
{
    use ResolvesImagePaths;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image',
        'cover_alt',
        'channel',
        'is_published',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public const KANALLAR = [
        'general'     => 'Genel',
        'electronics' => 'Elektronik',
        'health'      => 'Sağlık & Lens',
    ];

    /**
     * Yayında en az bir yazı var mı? Menüdeki "Rehberler" bağlantısı buna
     * göre gösterilir.
     *
     * ÖNBELLEĞE ALINMADI — bilerek. `(is_published, published_at)` indeksli
     * bir EXISTS sorgusu, layout'un halihazırda yaptığı ayar ve kategori
     * sorgularının yanında ölçülemeyecek kadar hafif. Önbellek denendi ve
     * `Post::where(...)->delete()` gibi sorgu kurucu silmeleri model olayı
     * üretmediği için bayat kalıyordu; taze kalma garantisi olmayan bir
     * önbellek, kazandırdığından fazlasını götürüyor.
     */
    public static function hasPublished(): bool
    {
        return static::published()->exists();
    }

    /** `ResolvesImagePaths` `image_path` bekliyor; yazıda alan adı farklı. */
    public function getImageUrlAttribute(): ?string
    {
        return $this->resolveImageUrl($this->cover_image);
    }

    /** Yayında olan yazılar. Listeleme ve sitemap bunu kullanır. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeChannel(Builder $query, ?string $channel): Builder
    {
        return $channel ? $query->where('channel', $channel) : $query;
    }

    /** Listede ve meta açıklamada kullanılan kısa özet. */
    public function getSummaryAttribute(): string
    {
        return filled($this->excerpt)
            ? $this->excerpt
            : Str::limit(trim(strip_tags($this->body)), 160);
    }

    /**
     * Tahmini okuma süresi (dakika). Dakikada ~200 kelime.
     * Listede gösterilince tıklama oranı artıyor.
     */
    public function getReadingMinutesAttribute(): int
    {
        $kelime = str_word_count(strip_tags($this->body));

        return max(1, (int) ceil($kelime / 200));
    }

    public function getChannelLabelAttribute(): string
    {
        return self::KANALLAR[$this->channel] ?? 'Genel';
    }
}
