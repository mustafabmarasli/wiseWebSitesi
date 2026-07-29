<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'zip_code',
        'identity_number',
        'billing_address',
        'billing_city',
        'is_corporate',
        'company_name',
        'tax_number',
        'tax_office',
        'payment_method',
        'payment_type',
        'payment_confirmed_at',
        'bank_transfer_discount',
        'shipping_method',
        'shipping_cost',
        'estimated_delivery_at',
        'total_amount',
        'currency',
        'status',
        'iyzico_token',
        'iyzico_payment_id',
        'iyzico_conversation_id',
        'iyzico_payment_status',
        'cart_snapshot',
        'coupon_code',
        'discount_amount',
        'province_id',
        'district_id',
        'neighborhood_id',
        'billing_province_id',
        'billing_district_id',
        'billing_neighborhood_id',
    ];

    protected $casts = [
        // KVKK: TC Kimlik No veritabanında şifreli tutulur.
        // APP_KEY değişirse mevcut kayıtlar okunamaz hâle gelir.
        'identity_number'       => 'encrypted',
        'cart_snapshot'         => 'array',
        'total_amount'          => 'decimal:2',
        'shipping_cost'         => 'decimal:2',
        'estimated_delivery_at' => 'datetime',
        'discount_amount'        => 'decimal:2',
        'bank_transfer_discount' => 'decimal:2',
        'payment_confirmed_at'   => 'datetime',
        'is_corporate'           => 'boolean',
    ];

    /** Havale/EFT ile ödenecek bir sipariş mi? */
    public function isBankTransfer(): bool
    {
        return $this->payment_type === 'bank_transfer';
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

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

    public function billingProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'billing_province_id');
    }

    public function billingDistrict(): BelongsTo
    {
        return $this->belongsTo(District::class, 'billing_district_id');
    }

    public function billingNeighborhood(): BelongsTo
    {
        return $this->belongsTo(Neighborhood::class, 'billing_neighborhood_id');
    }

    /**
     * Tam ad döndürür.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Maskelenmiş telefon numarası döndürür (Örn: 0555 *** 45 67).
     */
    public function getMaskedPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        if (strlen($phone) >= 10) {
            return substr($phone, 0, 4) . ' *** ' . substr($phone, 7, 2) . ' ' . substr($phone, 9);
        }
        return substr($this->phone, 0, 3) . '***' . substr($this->phone, -2);
    }

    /**
     * Maskelenmiş e-posta adresi döndürür (Örn: ex***@domain.com).
     */
    public function getMaskedEmailAttribute(): string
    {
        $email = $this->email;
        $parts = explode('@', $email);
        if (count($parts) === 2) {
            $name = $parts[0];
            $domain = $parts[1];
            $len = strlen($name);
            if ($len > 3) {
                return substr($name, 0, 2) . str_repeat('*', $len - 4) . substr($name, -2) . '@' . $domain;
            }
            return substr($name, 0, 1) . str_repeat('*', $len - 1) . '@' . $domain;
        }
        return $email;
    }
}
