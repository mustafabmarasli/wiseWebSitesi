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
        'slug',
        'description',
        'features',
        'price',
        'discount_price',
        'eski_fiyat',
        'satis_sayisi',
        'view_count',
        'stock',
        'image_path',
        'additional_images',
        'rating',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'features' => 'array',
        'additional_images' => 'array',
        'price' => 'float',
        'discount_price' => 'float',
        'eski_fiyat' => 'float',
        'satis_sayisi' => 'integer',
        'view_count' => 'integer',
        'rating' => 'float',
    ];

    public function views(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductView::class);
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

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
     {
         return $this->belongsTo(Category::class);
     }
 
     /**
      * Satışta geçerli olan fiyat: indirimli fiyat tanımlıysa o, değilse normal fiyat.
      *
      * Not: Sepet ve sipariş hesabı halen `price` üzerinden yürüyor. Buraya
      * geçilecekse CartController::syncCart() de aynı anda güncellenmeli, yoksa
      * vitrinde görünen fiyatla ödenen tutar birbirini tutmaz.
      */
     public function getActivePriceAttribute(): float
     {
         return $this->discount_price > 0
             ? (float) $this->discount_price
             : (float) $this->price;
     }

     /**
      * İndirim yüzdesi (eski fiyata göre). İndirim yoksa null.
      */
     public function getDiscountPercentAttribute(): ?int
     {
         $eski = (float) $this->eski_fiyat;

         if ($eski <= 0 || $eski <= $this->active_price) {
             return null;
         }

         return (int) round((1 - $this->active_price / $eski) * 100);
     }
}
