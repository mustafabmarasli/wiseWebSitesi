<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Gerçek müşteri yorumu — yalnızca teslim edilmiş bir siparişte o ürünü
 * satın almış müşteriden gelebilir (bkz. Product::canBeReviewedBy()).
 *
 * `Product.rating` (seed veri) İLE KARIŞTIRILMAMALI. Bu tablo ayrı bir
 * gerçeklik kaynağıdır; ikisi ürün detay sayfasında birbirine karışmadan
 * gösterilir.
 */
class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'comment',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'approved_at' => 'datetime',
    ];

    public const STATUSES = [
        'pending'  => 'Onay Bekliyor',
        'approved' => 'Yayında',
        'rejected' => 'Reddedildi',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function approve(): void
    {
        $this->update(['status' => 'approved', 'approved_at' => now()]);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected', 'approved_at' => null]);
    }

    /**
     * Ekranda gösterilecek isim: "Mustafa M." biçiminde.
     *
     * Tam ad hiçbir zaman herkese açık yorumda gösterilmez — soyadın tamamı
     * gizlenir, yalnızca baş harfi kalır (masked_email/masked_phone ile aynı
     * gizlilik ilkesi).
     */
    public function getReviewerNameAttribute(): string
    {
        $parcalar = preg_split('/\s+/', trim($this->user->name ?? ''));

        if (count($parcalar) < 2) {
            return $parcalar[0] ?? 'Müşteri';
        }

        $ilk = $parcalar[0];
        $soyadBasHarfi = mb_strtoupper(mb_substr(end($parcalar), 0, 1));

        return "{$ilk} {$soyadBasHarfi}.";
    }
}
