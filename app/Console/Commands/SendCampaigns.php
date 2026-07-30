<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Services\CampaignSender;
use Illuminate\Console\Command;

/**
 * Sıraya alınmış kampanyaları gönderir.
 *
 * Neden komut? Kuyruk `sync` (bkz. GELISTIRICI-NOTLARI madde 11): panelden
 * yüzlerce gönderim yapılmaya kalkışılsa istek zaman aşımına uğrar ve
 * gönderim yarıda kalır. Komut SSH'ten veya cron'dan çalıştırılır; yarıda
 * kalırsa tekrar çalıştırmak kaldığı yerden devam eder (gönderilenler
 * `campaign_deliveries` ile atlanır).
 */
class SendCampaigns extends Command
{
    protected $signature = 'campaigns:send {--id= : Yalnızca bu kampanyayı gönder}';

    protected $description = 'Gönderim sırasındaki toplu kampanyaları gönderir';

    public function handle(): int
    {
        $sorgu = Campaign::query()->where('status', 'queued');

        if ($this->option('id')) {
            $sorgu->where('id', $this->option('id'));
        }

        $kampanyalar = $sorgu->orderBy('id')->get();

        if ($kampanyalar->isEmpty()) {
            $this->info('Gönderim sırasında kampanya yok.');

            return self::SUCCESS;
        }

        $sender = new CampaignSender();

        foreach ($kampanyalar as $kampanya) {
            $this->line('Gönderiliyor: ' . $kampanya->title . ' (' . $kampanya->channel_label . ')');

            $engel = $sender->blocker($kampanya);

            if ($engel) {
                $this->error('  Atlandı: ' . $engel);
                continue;
            }

            $sonuc = $sender->send($kampanya);

            $this->info(sprintf(
                '  Bitti — gönderilen: %d, hata: %d, atlanan: %d',
                $sonuc['sent'],
                $sonuc['failed'],
                $sonuc['skipped'],
            ));
        }

        return self::SUCCESS;
    }
}
