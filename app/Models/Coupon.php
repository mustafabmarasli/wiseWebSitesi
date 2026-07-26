<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'max_uses_per_user',
        'used_count',
        'active',
        'expires_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'max_uses' => 'integer',
        'max_uses_per_user' => 'integer',
        'used_count' => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * Kupon süresinin dolup dolmadığını veya kullanım limitine ulaşıp ulaşmadığını kontrol eder.
     */
    public function isExpiredOrLimitReached($userId = null, $email = null): bool
    {
        if (!$this->active) {
            return true;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return true;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return true;
        }

        // Kullanıcı başına kullanım limiti kontrolü
        if ($this->max_uses_per_user !== null && ($userId || $email)) {
            $userOrdersCount = \App\Models\Order::where('coupon_code', $this->code)
                ->where('status', 'paid')
                ->where(function($query) use ($userId, $email) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    }
                    if ($email) {
                        $query->orWhere('email', $email);
                    }
                })
                ->count();

            if ($userOrdersCount >= $this->max_uses_per_user) {
                return true;
            }
        }

        return false;
    }
}
