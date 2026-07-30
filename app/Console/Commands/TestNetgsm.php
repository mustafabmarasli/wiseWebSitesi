<?php

namespace App\Console\Commands;

use App\Models\MarketingConsent;
use App\Models\Setting;
use App\Services\NetgsmSmsSender;
use Illuminate\Console\Command;

/**
 * Netgsm ayarlarını gerçek kampanya beklemeden sınar.
 */
class TestNetgsm extends Command
{
    protected $signature = 'netgsm:test {telefon? : Deneme SMS gönderilecek numara}';

    protected $description = 'Netgsm SMS ayarlarını sınar ve isteğe bağlı deneme SMS gönderir';

    public function handle(): int
    {
        $sender = new NetgsmSmsSender();

        if (! $sender->isConfigured()) {
            $this->error('Netgsm yapılandırılmamış.');
            $this->newLine();
            $this->line('.env dosyasına şunları ekleyin:');
            $this->line('  NETGSM_USERCODE=850XXXXXXX     (Netgsm abone numaranız)');
            $this->line('  NETGSM_PASSWORD=...            (API şifresi — panel şifresi değil)');
            $this->line('  NETGSM_HEADER=WISE             (Netgsm\'de ONAYLI gönderici adı)');
            $this->newLine();
            $this->line('Ardından: php artisan config:clear');

            return self::FAILURE;
        }

        $this->info('Netgsm yapılandırması bulundu.');
        $this->line('  Gönderici adı: ' . config('services.netgsm.header'));
        $this->newLine();

        // Ana şalter ayrı bir karar; token doğru olsa da kapalı olabilir.
        $this->line('Toplu gönderim şalteri: ' . (Setting::current()->marketing_sending_enabled ? 'AÇIK' : 'KAPALI'));
        $this->line('SMS onayı olan alıcı: ' . MarketingConsent::granted()->channel('sms')->count());
        $this->newLine();

        $telefon = $this->argument('telefon');

        if (! $telefon) {
            $this->line('Deneme SMS göndermek için: php artisan netgsm:test 05321112233');

            return self::SUCCESS;
        }

        $normal = MarketingConsent::normalizePhone($telefon);
        $this->line('Gönderiliyor: ' . $normal);

        $sonuc = $sender->send($normal, 'Buy WISEly SMS ayar testi. Bu mesaji gordugunuzde ayarlar dogru demektir.');

        if (! $sonuc['ok']) {
            $this->error('Gönderilemedi: ' . $sonuc['message']);

            return self::FAILURE;
        }

        $this->info($sonuc['message']);

        return self::SUCCESS;
    }
}
