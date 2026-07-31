<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kargo takip numarası ve linki.
     *
     * Kargo firmasına göre otomatik link ÜRETİLMEZ — her firmanın takip
     * adresi farklı formatta ve zamanla değişebiliyor; yanlış tahmin edilen
     * bir URL müşteriye kırık bağlantı olarak gider. Bunun yerine yönetici
     * numarayı VE tam takip linkini kargo firmasının sitesinden alıp
     * doğrudan yapıştırır.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('tracking_number')->nullable()->after('shipping_method');
            $table->string('tracking_url')->nullable()->after('tracking_number');
            // Durum "Kargoya Verildi"ye ilk geçtiğinde bir kez e-posta
            // gönderilir. Bu alan olmadan her düzenlemede tekrar giderdi.
            $table->timestamp('shipped_notified_at')->nullable()->after('tracking_url');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tracking_number', 'tracking_url', 'shipped_notified_at']);
        });
    }
};
