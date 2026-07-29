<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * "Stok gelince haber ver" kaydı.
 *
 * `notified_at` doluysa iş bitmiştir: e-posta gitmiştir. Ürün tekrar
 * tükenip müşteri yeniden kaydolursa bu alan null'a çekilir, kayıt
 * yeniden bekleyenler arasına girer.
 */
class StockNotification extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'email',
        'notified_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Henüz e-posta gönderilmemiş kayıtlar. */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('notified_at');
    }
}
