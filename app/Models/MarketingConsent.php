<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Ticari elektronik ileti onayı.
 *
 * DİKKAT: Bu KVKK onayı DEĞİLDİR. KVKK verinin işlenmesini, bu kayıt ise
 * 6563 sayılı kanun kapsamında pazarlama iletisi GÖNDERİLMESİNİ kapsar.
 * İkisi ayrı ayrı alınır; "aydınlatma metnini okudum" kutusu buranın yerine
 * geçmez.
 */
class MarketingConsent extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'phone',
        'channel',
        'status',
        'source',
        'ip_address',
        'consented_at',
        'revoked_at',
        'synced_to_iys_at',
        'unsubscribe_token',
    ];

    protected $casts = [
        'consented_at'     => 'datetime',
        'revoked_at'       => 'datetime',
        'synced_to_iys_at' => 'datetime',
    ];

    /**
     * İYS kanal karşılıkları. WhatsApp ayrı bir kanal değildir; İYS'de
     * "MESAJ" altında değerlendirilir.
     */
    public const KANALLAR = [
        'email' => 'E-posta',
        'sms'   => 'SMS / WhatsApp',
        'call'  => 'Telefonla arama',
    ];

    public const IYS_KARSILIKLARI = [
        'email' => 'E-POSTA',
        'sms'   => 'MESAJ',
        'call'  => 'ARAMA',
    ];

    /** Onayın nereden alındığı — İYS "onay kaynağı" alanına karşılık gelir. */
    public const KAYNAKLAR = [
        'register'    => 'Üyelik formu',
        'checkout'    => 'Ödeme adımı',
        'admin'       => 'Panelden elle',
        'unsubscribe' => 'Çıkış sayfası',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Gönderim yapılabilecek onaylar. */
    public function scopeGranted(Builder $query): Builder
    {
        return $query->where('status', 'granted');
    }

    public function scopeChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    public function isGranted(): bool
    {
        return $this->status === 'granted';
    }

    public function getChannelLabelAttribute(): string
    {
        return self::KANALLAR[$this->channel] ?? $this->channel;
    }

    /** Ekranda gösterilecek kişi bilgisi. */
    public function getContactAttribute(): string
    {
        return $this->email ?: ($this->phone ?: '—');
    }

    /**
     * Telefonu tek biçime indirir: 0532 111 22 33 → 905321112233.
     *
     * Aynı numaranın farklı yazımlarla iki kez kaydedilmesi, "abonelikten
     * çıktım ama mesaj gelmeye devam ediyor" şikâyetinin en yaygın sebebi.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $rakamlar = preg_replace('/\D/', '', $phone);

        if (blank($rakamlar)) {
            return null;
        }

        // 0532... → 90532...
        if (str_starts_with($rakamlar, '0')) {
            $rakamlar = '90' . substr($rakamlar, 1);
        }

        // 532... (10 hane, ülke kodsuz)
        if (strlen($rakamlar) === 10) {
            $rakamlar = '90' . $rakamlar;
        }

        return $rakamlar;
    }

    /**
     * Onay verir. Aynı kişi + kanal için ikinci satır açılmaz, mevcut kayıt
     * tazelenir; böylece çıkıp tekrar giren kişinin geçmişi bozulmaz.
     */
    public static function grant(
        string $channel,
        ?string $email = null,
        ?string $phone = null,
        string $source = 'register',
        ?int $userId = null,
        ?string $ip = null,
    ): ?self {
        $email = filled($email) ? mb_strtolower(trim($email)) : null;
        $phone = self::normalizePhone($phone);

        // Kimliksiz onay tutulamaz.
        if (blank($email) && blank($phone)) {
            return null;
        }

        $kayit = static::query()
            ->where('channel', $channel)
            ->when($email, fn ($q) => $q->where('email', $email))
            ->when(! $email, fn ($q) => $q->where('phone', $phone))
            ->first();

        if ($kayit) {
            $kayit->update([
                'status'       => 'granted',
                'source'       => $source,
                'ip_address'   => $ip,
                'consented_at' => now(),
                'revoked_at'   => null,
                'user_id'      => $userId ?? $kayit->user_id,
                'phone'        => $phone ?: $kayit->phone,
                // Onay tazelendi: İYS'ye yeniden yüklenmeli.
                'synced_to_iys_at' => null,
            ]);

            return $kayit;
        }

        return static::create([
            'user_id'           => $userId,
            'email'             => $email,
            'phone'             => $phone,
            'channel'           => $channel,
            'status'            => 'granted',
            'source'            => $source,
            'ip_address'        => $ip,
            'consented_at'      => now(),
            'unsubscribe_token' => Str::random(48),
        ]);
    }

    /** Onayı geri çeker. Kayıt SİLİNMEZ — ispat için tarihçe korunur. */
    public function revoke(string $source = 'unsubscribe'): void
    {
        $this->update([
            'status'           => 'revoked',
            'source'           => $source,
            'revoked_at'       => now(),
            'synced_to_iys_at' => null,
        ]);
    }

    /** Bu kişinin tüm kanallardaki onayları — çıkış sayfası hepsini gösterir. */
    public function siblings()
    {
        return static::query()
            ->when($this->email, fn ($q) => $q->where('email', $this->email))
            ->when(! $this->email, fn ($q) => $q->where('phone', $this->phone))
            ->orderBy('channel')
            ->get();
    }

    public function unsubscribeUrl(): string
    {
        return route('marketing.unsubscribe', $this->unsubscribe_token);
    }
}
