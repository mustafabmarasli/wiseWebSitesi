<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // payment_method serbest metindir ("iyzico Kredi Kartı" gibi) ve
        // görüntüleme içindir. Kod akışının metin karşılaştırmasına
        // dayanmaması için makine tarafından okunan ayrı bir alan tutulur.
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_type', 20)->default('card')->after('payment_method');
            $table->decimal('bank_transfer_discount', 10, 2)->default(0)->after('discount_amount');
            $table->timestamp('payment_confirmed_at')->nullable()->after('payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'bank_transfer_discount', 'payment_confirmed_at']);
        });
    }
};
