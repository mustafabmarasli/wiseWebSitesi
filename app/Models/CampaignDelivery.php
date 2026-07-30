<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignDelivery extends Model
{
    protected $fillable = [
        'campaign_id',
        'marketing_consent_id',
        'contact',
        'status',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function consent(): BelongsTo
    {
        return $this->belongsTo(MarketingConsent::class, 'marketing_consent_id');
    }
}
