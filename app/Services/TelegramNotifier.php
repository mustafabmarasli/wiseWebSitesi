<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Yeni siparişleri Telegram üzerinden anlık bildirir.
 *
 * E-posta tek başına güvenilir bir bildirim kanalı değil: SMTP kesintisinde
 * sipariş kaydedilir ama haber alınamaz, ayrıca spam klasörüne düşebilir.
 * Telegram bunu tamamlar — ikisi birbirinin yedeği.
 *
 * Yapılandırma yoksa sessizce devre dışıdır; bu sayede yerel geliştirmede
 * ve testlerde ayrıca bir şey yapmak gerekmez.
 */
class TelegramNotifier
{
    /** Ağ beklemesi ödeme akışını kilitlemesin. */
    private const TIMEOUT_SECONDS = 5;

    public function isConfigured(): bool
    {
        return filled(config('services.telegram.bot_token'))
            && filled(config('services.telegram.chat_id'));
    }

    /**
     * Yeni sipariş bildirimi gönderir.
     *
     * Gönderim başarısız olursa yalnızca loglanır: bildirim gitmedi diye
     * müşterinin siparişi düşmemeli.
     */
    public function notifyNewOrder(Order $order): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        return $this->send($this->buildOrderMessage($order));
    }

    /**
     * Yeni müşteri kaydı bildirimi.
     *
     * Panelden kapatılabilir (Site Ayarları → Bildirimler). Kapalıysa hiçbir
     * şey yapılmaz. Bildirim, üyelik işlemini ASLA düşürmemeli — bu yüzden
     * çağıran taraf sonucu yok sayar, hatalar yalnızca loglanır.
     */
    public function notifyNewCustomer(User $user): bool
    {
        if (! Setting::current()->notifiesNewCustomer()) {
            return false;
        }

        if (! $this->isConfigured()) {
            return false;
        }

        return $this->send($this->buildCustomerMessage($user));
    }

    public function send(string $message): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->post('https://api.telegram.org/bot' . config('services.telegram.bot_token') . '/sendMessage', [
                    'chat_id'    => config('services.telegram.chat_id'),
                    'text'       => $message,
                    'parse_mode' => 'HTML',
                    // Panel bağlantısı için dev bir önizleme kartı açılmasın
                    'disable_web_page_preview' => true,
                ]);

            if ($response->failed()) {
                Log::warning('Telegram bildirimi gönderilemedi', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Telegram bildirimi gönderilemedi: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Yeni müşteri mesajı.
     *
     * Telefon, adres gibi ek kişisel veri BİLEREK yok: Telegram mesajı
     * sunucu dışına çıkan bir kayıttır, bildirim için ad ve e-posta yeterli.
     */
    private function buildCustomerMessage(User $user): string
    {
        $satirlar = [
            '👤 <b>YENİ MÜŞTERİ KAYDI</b>',
            '',
            '<b>Ad:</b> ' . e($user->name),
            '<b>E-posta:</b> ' . e($user->email),
            '<b>Tarih:</b> ' . ($user->created_at ?? now())->format('d.m.Y H:i'),
            '<b>Toplam müşteri:</b> ' . User::where('is_admin', false)->count(),
            '',
            route('filament.admin.resources.users.index'),
        ];

        return implode("\n", $satirlar);
    }

    private function buildOrderMessage(Order $order): string
    {
        $order->loadMissing('items');

        $bekliyor = $order->status === OrderStatus::Pending->value;

        $satirlar = [
            $bekliyor ? '🟡 <b>YENİ SİPARİŞ — ÖDEME BEKLENİYOR</b>' : '🟢 <b>YENİ SİPARİŞ — ÖDEME ALINDI</b>',
            '',
            '<b>No:</b> ' . e($order->display_number),
            '<b>Müşteri:</b> ' . e($order->full_name),
            '<b>Telefon:</b> ' . e($order->phone),
            '<b>Tutar:</b> ' . number_format((float) $order->total_amount, 2, ',', '.') . ' TL',
            '<b>Ödeme:</b> ' . e($order->payment_method),
            '',
            '<b>Ürünler:</b>',
        ];

        foreach ($order->items as $item) {
            $satirlar[] = '• ' . e($item->product_name) . ' × ' . $item->quantity;
        }

        if ($bekliyor) {
            $satirlar[] = '';
            $satirlar[] = '⚠️ Para hesaba geçmeden kargoya vermeyin.';
        }

        $satirlar[] = '';
        $satirlar[] = route('filament.admin.resources.orders.view', $order->id);

        return implode("\n", $satirlar);
    }
}
