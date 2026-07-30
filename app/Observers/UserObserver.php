<?php

namespace App\Observers;

use App\Models\User;
use App\Services\TelegramNotifier;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Yeni müşteri kaydında Telegram bildirimi.
     *
     * Observer kullanılıyor çünkü kullanıcı DÖRT ayrı yerde oluşuyor:
     * üyelik formu, Google ile giriş, misafir siparişinden üyelik ve
     * `admin:create` komutu. Bildirimi dört yere kopyalasaydık biri
     * eklendiğinde unutulurdu.
     */
    public function created(User $user): void
    {
        // Yönetici hesabı müşteri değil.
        if ($user->is_admin) {
            return;
        }

        // Bildirim gönderimi üyelik işlemini ASLA düşürmemeli: müşteri
        // hesabını açtı, iş bitti. Telegram katmanındaki hata loglanır.
        try {
            (new TelegramNotifier())->notifyNewCustomer($user);
        } catch (\Throwable $e) {
            Log::warning('Yeni müşteri bildirimi işlenemedi', [
                'user_id' => $user->id,
                'hata'    => $e->getMessage(),
            ]);
        }
    }
}
