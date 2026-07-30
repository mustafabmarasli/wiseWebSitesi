<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Toplu gönderim kampanyası.
 *
 * Kampanya kendisi gönderim YAPMAZ; `CampaignSender` yapar ve onay
 * denetimi orada uygulanır.
 */
class Campaign extends Model
{
    protected $fillable = [
        'channel',
        'title',
        'subject',
        'body',
        'status',
        'sent_count',
        'failed_count',
        'skipped_count',
        'started_at',
        'completed_at',
        'last_error',
        'created_by',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const KANALLAR = [
        'email' => 'E-posta',
        'sms'   => 'SMS',
    ];

    public const DURUMLAR = [
        'draft'   => 'Taslak',
        'queued'  => 'Gönderim sırasında',
        'sending' => 'Gönderiliyor',
        'sent'    => 'Gönderildi',
        'failed'  => 'Başarısız',
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(CampaignDelivery::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function getChannelLabelAttribute(): string
    {
        return self::KANALLAR[$this->channel] ?? $this->channel;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::DURUMLAR[$this->status] ?? $this->status;
    }

    /** Bu kampanyanın gideceği onaylı kişi sayısı (tahmini). */
    public function audienceCount(): int
    {
        return MarketingConsent::granted()
            ->channel($this->channel)
            ->when($this->channel === 'email', fn ($q) => $q->whereNotNull('email'))
            ->when($this->channel === 'sms', fn ($q) => $q->whereNotNull('phone'))
            ->count();
    }
}
