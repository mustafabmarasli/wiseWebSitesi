<?php

namespace App\Console\Commands;

use App\Services\TelegramNotifier;
use Illuminate\Console\Command;

/**
 * Telegram bildiriminin çalışıp çalışmadığını sipariş beklemeden sınar.
 *
 * Token ve chat id doğru mu, bot gerçekten mesaj atabiliyor mu — bunu
 * öğrenmek için gerçek bir sipariş vermek gerekmesin.
 */
class TestTelegram extends Command
{
    protected $signature = 'telegram:test';

    protected $description = 'Telegram bildirim ayarlarını sınar ve deneme mesajı gönderir';

    public function handle(): int
    {
        $notifier = new TelegramNotifier();

        if (! $notifier->isConfigured()) {
            $this->error('Telegram yapılandırılmamış.');
            $this->newLine();
            $this->line('.env dosyasına şunları ekleyin:');
            $this->line('  TELEGRAM_BOT_TOKEN=...');
            $this->line('  TELEGRAM_CHAT_ID=...');
            $this->newLine();
            $this->line('Token: Telegram\'da @BotFather ile konuşup /newbot yazın.');
            $this->line('Chat ID: Botunuza bir mesaj atın, sonra şu adresi açın:');
            $this->line('  https://api.telegram.org/bot<TOKEN>/getUpdates');
            $this->line('  Dönen JSON içindeki "chat":{"id":...} değeri sizin chat id\'nizdir.');

            return self::FAILURE;
        }

        $this->info('Yapılandırma bulundu, deneme mesajı gönderiliyor...');

        $basarili = $notifier->send(
            "✅ <b>Buy WISEly bildirim testi</b>\n\n"
            . "Bu mesajı görüyorsanız sipariş bildirimleri çalışıyor demektir.\n"
            . 'Gönderim zamanı: ' . now()->format('d.m.Y H:i')
        );

        if (! $basarili) {
            $this->error('Mesaj gönderilemedi. Ayrıntı için storage/logs/laravel.log dosyasına bakın.');

            return self::FAILURE;
        }

        $this->info('Mesaj gönderildi. Telegram\'ı kontrol edin.');

        return self::SUCCESS;
    }
}
