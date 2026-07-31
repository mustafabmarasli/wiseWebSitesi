<?php

namespace App\Models;

use App\Models\Concerns\ResolvesImagePaths;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;
    use ResolvesImagePaths;

    protected $fillable = [
        'category_id',
        'name',
        'brand',
        'gtin',
        'slug',
        'description',
        'features',
        'price',
        'eski_fiyat',
        'satis_sayisi',
        'view_count',
        'stock',
        'image_path',
        'additional_images',
        'rating',
        'is_featured',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'features' => 'array',
        'additional_images' => 'array',
        'price' => 'float',
        'eski_fiyat' => 'float',
        'satis_sayisi' => 'integer',
        'view_count' => 'integer',
        'rating' => 'float',
        'is_featured' => 'boolean',
    ];

    public function views(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductView::class);
    }

    /** Bu ürün için açılmış tüm "stok gelince haber ver" kayıtları. */
    public function stockNotifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockNotification::class);
    }

    /**
     * Henüz bilgilendirilmemiş bekleyenler. Panelde "kaç kişi bekliyor"
     * sütunu ve stok girişindeki gönderim bu ilişkiden yürür.
     */
    public function pendingStockNotifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->stockNotifications()->whereNull('notified_at');
    }

    /**
     * Görüntülenme başına satış oranı (%). Hiç görüntülenmemişse null.
     */
    public function getConversionRateAttribute(): ?float
    {
        if (!$this->view_count) {
            return null;
        }

        return round(($this->satis_sayisi / $this->view_count) * 100, 1);
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /** Herkese açık gösterilen, yönetici onaylı yorumlar. */
    public function approvedReviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->reviews()->approved();
    }

    /**
     * Bu kullanıcı bu ürüne yorum yazabilir mi?
     *
     * İKİ şart birden gerekir: (1) bu ürünü içeren TESLİM EDİLMİŞ bir
     * siparişi olmalı, (2) daha önce bu ürüne yorum yazmamış olmalı.
     * "Ödendi" veya "Kargoda" yeterli değil — ürünü eline almadan
     * deneyimlemiş sayılmaz.
     */
    public function canBeReviewedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->reviews()->where('user_id', $user->id)->exists()) {
            return false;
        }

        return OrderItem::where('product_id', $this->id)
            ->whereHas('order', fn ($q) => $q
                ->where('user_id', $user->id)
                ->where('status', \App\Enums\OrderStatus::Delivered->value))
            ->exists();
    }

    /** Onaylı yorum sayısı. */
    public function getRealReviewsCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    /** Onaylı yorumların ortalaması. Hiç yoksa null. */
    public function getRealAverageRatingAttribute(): ?float
    {
        $ortalama = $this->approvedReviews()->avg('rating');

        return $ortalama !== null ? round((float) $ortalama, 1) : null;
    }

    public function hasRealReviews(): bool
    {
        return $this->real_reviews_count > 0;
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
     {
         return $this->belongsTo(Category::class);
     }
 
     /**
      * Satışta geçerli fiyat.
      *
      * Tek fiyat alanı vardır: `price`. İndirim, `eski_fiyat` üzeri çizilerek
      * gösterilir. Vitrin, ürün kartı, detay, taksit, sepet ve sipariş —
      * hepsi bu değeri kullanır. İkinci bir fiyat alanı EKLEME; "hangisi
      * geçerli?" belirsizliği gösterilen fiyatla tahsil edilen tutarı ayırır.
      */
     public function getActivePriceAttribute(): float
     {
         return (float) $this->price;
     }

     /**
      * İndirim yüzdesi (eski fiyata göre). İndirim yoksa null.
      */
     public function getDiscountPercentAttribute(): ?int
     {
         $eski  = (float) $this->eski_fiyat;
         $simdi = (float) $this->price;

         if ($eski <= 0 || $eski <= $simdi) {
             return null;
         }

         return (int) round((1 - $simdi / $eski) * 100);
     }
}
