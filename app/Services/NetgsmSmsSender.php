<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Netgsm üzerinden SMS gönderir.
 *
 * Yapılandırma yoksa (`services.netgsm.*` boş) sessizce devre dışıdır —
 * yerelde ve testlerde ayrıca bir şey yapmak gerekmez.
 *
 * DİKKAT: Bu sınıf ONAY KONTROLÜ YAPMAZ. Onay denetimi `CampaignSender`
 * içindedir; buraya doğrudan gelen bir çağrı izinsiz gönderim yapabilir.
 * Toplu gönderim için daima `CampaignSender` kullan.
 */
class NetgsmSmsSender
{
    private const ENDPOINT = 'https://api.netgsm.com.tr/sms/send/get';

    /** Ağ beklemesi panelin tamamını kilitlemesin. */
    private const TIMEOUT_SECONDS = 15;

    /**
     * Netgsm hata kodları. Sayısal kodlar tek başına anlaşılmıyor; panelde
     * "80" yerine ne yapılacağı yazsın diye burada karşılıkları tutuluyor.
     */
    private const HATA_KODLARI = [
        '20' => 'Mesaj metni çok uzun veya karakter sorunu var.',
        '30' => 'Kullanıcı adı/şifre hatalı ya da API erişim izniniz yok.',
        '40' => 'Gönderici adı (başlık) sistemde tanımlı değil.',
        '50' => 'Abone hesabınız İYS kontrollü; numara İYS\'de onaylı değil.',
        '51' => 'İYS marka bilgisi eksik veya hatalı.',
        '70' => 'Hatalı sorgulama — parametrelerden biri eksik veya yanlış.',
        '80' => 'Gönderim sınırı aşıldı.',
        '85' => 'Aynı numaraya 1 dakikada en fazla 20 görev gönderilebilir.',
        '100' => 'Sistem hatası.',
        '101' => 'Sistem hatası.',
    ];

    public function isConfigured(): bool
    {
        return filled(config('services.netgsm.usercode'))
            && filled(config('services.netgsm.password'))
            && filled(config('services.netgsm.header'));
    }

    /**
     * Tek numaraya SMS gönderir.
     *
     * @param  string  $phone  Normalize edilmiş numara (905321112233).
     * @return array{ok: bool, message: string}
     */
    public function send(string $phone, string $message): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'message' => 'Netgsm yapılandırılmamış (.env içinde NETGSM_* değerleri yok).'];
        }

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)->get(self::ENDPOINT, [
                'usercode'  => config('services.netgsm.usercode'),
                'password'  => config('services.netgsm.password'),
                'gsmno'     => $phone,
                'message'   => $message,
                'msgheader' => config('services.netgsm.header'),
                'dil'       => 'TR',
            ]);

            if ($response->failed()) {
                return ['ok' => false, 'message' => 'Netgsm sunucusuna ulaşılamadı (HTTP ' . $response->status() . ').'];
            }

            return $this->yanitiCoz(trim($response->body()));
        } catch (\Throwable $e) {
            Log::error('Netgsm SMS gönderilemedi', ['phone' => $phone, 'hata' => $e->getMessage()]);

            return ['ok' => false, 'message' => 'Bağlantı hatası: ' . $e->getMessage()];
        }
    }

    /**
     * Netgsm yanıtı "00 123456789" (başarılı) veya "30" (hata) biçimindedir.
     * İlk parça durum kodudur.
     */
    private function yanitiCoz(string $body): array
    {
        $parcalar = preg_split('/\s+/', $body);
        $kod      = $parcalar[0] ?? '';

        // 00 ve 01/02 başarı kodlarıdır (01/02 zamanlanmış gönderimde döner).
        if (in_array($kod, ['00', '01', '02'], true)) {
            return ['ok' => true, 'message' => 'Gönderildi' . (isset($parcalar[1]) ? ' (görev no: ' . $parcalar[1] . ')' : '')];
        }

        return [
            'ok'      => false,
            'message' => self::HATA_KODLARI[$kod] ?? ('Netgsm hata kodu: ' . ($kod ?: 'bilinmiyor')),
        ];
    }
}
