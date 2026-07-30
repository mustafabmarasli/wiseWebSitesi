<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Yeni müşteri kaydında Telegram bildirimi açık/kapalı.
     *
     * Varsayılan KAPALI: mevcut kurulumda kimse bu bildirimi istemedi,
     * kod yayına alınınca aniden bildirim akmaya başlaması sürpriz olur.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('new_customer_telegram_enabled')
                ->default(false)
                ->after('consulting_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('new_customer_telegram_enabled');
        });
    }
};
