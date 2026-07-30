<?php

namespace App\Services;

use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\CampaignDelivery;
use App\Models\MarketingConsent;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Toplu gönderimi yürütür.
 *
 * ONAY DENETİMİNİN TEK YERİ BURASIDIR. Gönderim listesi doğrudan
 * `marketing_consents` üzerinden kurulur; elle liste verilemez. Böylece
 * "yanlış listeye gönderdim" hatası mümkün değil.
 *
 * Her ileti ALTINA ÇIKIŞ BAĞLANTISI eklenir — 6563 sayılı kanun bunu
 * zorunlu tutuyor ve unutulmaması için gövdeye burada ekleniyor, panelde
 * yazana bırakılmıyor.
 */
class CampaignSender
{
    /** Tek seferde işlenecek alıcı sayısı. */
    private const CHUNK = 100;

    public function __construct(private ?NetgsmSmsSender $sms = null)
    {
        $this->sms ??= new NetgsmSmsSender();
    }

    /**
     * Gönderim yapılabilir mi? Yapılamıyorsa sebebini döndürür.
     *
     * @return string|null  null = gönderilebilir.
     */
    public function blocker(Campaign $campaign): ?string
    {
        if (! Setting::current()->marketing_sending_enabled) {
            return 'Toplu gönderim kapalı. Panel → Site Ayarları → Bildirimler bölümünden açın.';
        }

        if ($campaign->channel === 'sms' && ! $this->sms->isConfigured()) {
            return 'Netgsm yapılandırılmamış (.env içinde NETGSM_USERCODE, NETGSM_PASSWORD, NETGSM_HEADER).';
        }

        if ($campaign->audienceCount() === 0) {
            return 'Bu kanalda onaylı alıcı yok. Onaysız kişiye gönderim yapılamaz.';
        }

        return null;
    }

    /**
     * Kampanyayı gönderir.
     *
     * @return array{sent: int, failed: int, skipped: int}
     */
    public function send(Campaign $campaign): array
    {
        $engel = $this->blocker($campaign);

        if ($engel) {
            $campaign->update(['status' => 'failed', 'last_error' => $engel, 'completed_at' => now()]);

            return ['sent' => 0, 'failed' => 0, 'skipped' => 0];
        }

        $campaign->update([
            'status'     => 'sending',
            'started_at' => now(),
            'last_error' => null,
        ]);

        $sayac = ['sent' => 0, 'failed' => 0, 'skipped' => 0];

        MarketingConsent::granted()
            ->channel($campaign->channel)
            ->when($campaign->channel === 'email', fn ($q) => $q->whereNotNull('email'))
            ->when($campaign->channel === 'sms', fn ($q) => $q->whereNotNull('phone'))
            ->chunkById(self::CHUNK, function ($onaylar) use ($campaign, &$sayac) {
                foreach ($onaylar as $onay) {
                    $sonuc = $this->tekGonder($campaign, $onay);
                    $sayac[$sonuc]++;
                }

                $campaign->update([
                    'sent_count'    => $sayac['sent'],
                    'failed_count'  => $sayac['failed'],
                    'skipped_count' => $sayac['skipped'],
                ]);
            });

        $campaign->update([
            'status'        => 'sent',
            'sent_count'    => $sayac['sent'],
            'failed_count'  => $sayac['failed'],
            'skipped_count' => $sayac['skipped'],
            'completed_at'  => now(),
        ]);

        return $sayac;
    }

    /**
     * Tek alıcıya gönderir.
     *
     * @return string  'sent' | 'failed' | 'skipped'
     */
    private function tekGonder(Campaign $campaign, MarketingConsent $onay): string
    {
        $hedef = $campaign->channel === 'email' ? $onay->email : $onay->phone;

        // Gönderim sürerken onayını geri çeken olabilir; her alıcıda taze
        // kontrol yapılır. Listenin başında yapılan tek kontrol yetmez.
        if (! $onay->fresh()?->isGranted()) {
            $this->kaydet($campaign, $onay, $hedef, 'skipped', 'Onay gönderim sırasında geri çekildi.');

            return 'skipped';
        }

        // Aynı kampanyada aynı kişiye ikinci kez gönderme (komut yeniden
        // çalıştırılırsa kaldığı yerden devam etsin).
        $mevcut = CampaignDelivery::where('campaign_id', $campaign->id)
            ->where('contact', $hedef)
            ->first();

        if ($mevcut && $mevcut->status === 'sent') {
            return 'sent';
        }

        try {
            if ($campaign->channel === 'email') {
                Mail::to($hedef)->send(new CampaignMail($campaign, $onay));
            } else {
                $sonuc = $this->sms->send($hedef, $this->smsMetni($campaign, $onay));

                if (! $sonuc['ok']) {
                    $this->kaydet($campaign, $onay, $hedef, 'failed', $sonuc['message']);

                    return 'failed';
                }
            }

            $this->kaydet($campaign, $onay, $hedef, 'sent');

            return 'sent';
        } catch (\Throwable $e) {
            Log::error('Kampanya gönderimi başarısız', [
                'campaign_id' => $campaign->id,
                'contact'     => $hedef,
                'hata'        => $e->getMessage(),
            ]);

            $this->kaydet($campaign, $onay, $hedef, 'failed', $e->getMessage());

            return 'failed';
        }
    }

    /**
     * SMS metni + çıkış bilgisi.
     *
     * SMS'te uzun bağlantı karakter yiyor ama çıkış yolu göstermek zorunlu;
     * en kısa yol kısa bir yönerge + bağlantı.
     */
    private function smsMetni(Campaign $campaign, MarketingConsent $onay): string
    {
        return trim($campaign->body) . "\n\nÇıkış: " . $onay->unsubscribeUrl();
    }

    private function kaydet(Campaign $campaign, MarketingConsent $onay, string $hedef, string $durum, ?string $hata = null): void
    {
        CampaignDelivery::updateOrCreate(
            ['campaign_id' => $campaign->id, 'contact' => $hedef],
            [
                'marketing_consent_id' => $onay->id,
                'status'               => $durum,
                'error'                => $hata,
                'sent_at'              => $durum === 'sent' ? now() : null,
            ],
        );
    }

    /**
     * Tek adrese/numaraya deneme gönderimi.
     *
     * Onay aranmaz çünkü hedef yöneticinin kendisidir; toplu listeye
     * dokunmaz ve kayıt açmaz.
     */
    public function sendTest(Campaign $campaign, string $hedef): array
    {
        if ($campaign->channel === 'sms') {
            if (! $this->sms->isConfigured()) {
                return ['ok' => false, 'message' => 'Netgsm yapılandırılmamış.'];
            }

            return $this->sms->send(
                MarketingConsent::normalizePhone($hedef) ?? $hedef,
                trim($campaign->body) . "\n\n(DENEME GÖNDERİMİ)",
            );
        }

        try {
            Mail::to($hedef)->send(new CampaignMail($campaign, null));

            return ['ok' => true, 'message' => 'Deneme e-postası gönderildi: ' . $hedef];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Gönderilemedi: ' . $e->getMessage()];
        }
    }
}
