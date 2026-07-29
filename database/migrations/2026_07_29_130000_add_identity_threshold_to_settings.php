<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fatura düzenleme haddi her yıl yeniden değerleme ile değişiyor
        // (2025: 9.900 TL, 2026: 12.000 TL). Koda gömülürse her yıl kod
        // değişikliği gerekirdi; panelden güncellenebilir olmalı.
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('identity_required_threshold', 12, 2)->default(12000)->after('card_payment_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('identity_required_threshold');
        });
    }
};
