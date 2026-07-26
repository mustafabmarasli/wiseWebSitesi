<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'first_name',
        'last_name',
        'phone',
        'address',
        'city',
        'province_id',
        'district_id',
        'neighborhood_id',
        'zip_code',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class);
    }

    /**
     * Ödeme formunun kayıtlı adresten otomatik dolması için gereken alanlar.
     *
     * @return array<string, mixed>
     */
    public function toCheckoutPayload(): array
    {
        return [
            'first_name'      => $this->first_name,
            'last_name'       => $this->last_name,
            'phone'           => $this->phone,
            'province_id'     => $this->province_id,
            'district_id'     => $this->district_id,
            'neighborhood_id' => $this->neighborhood_id,
            'address_detail'  => $this->address,
            'zip_code'        => $this->zip_code,
            // Ekranda gösterilecek okunabilir konum adları
            'province_name'     => $this->province?->name,
            'district_name'     => $this->district?->name,
            'neighborhood_name' => $this->neighborhood?->name,
        ];
    }

    /**
     * Tam ad döndürür.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
