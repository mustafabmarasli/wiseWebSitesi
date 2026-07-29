<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Havale/EFT ile ödeme ve banka bilgileri panelden yönetilir.
        // Kart ödemesi varsayılan olarak KAPALI başlar: iyzico başvurusu
        // onaylanana kadar müşteriye kart seçeneği sunulmamalı.
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('bank_transfer_enabled')->default(true)->after('consulting_enabled');
            $table->decimal('bank_transfer_discount_percent', 5, 2)->default(0)->after('bank_transfer_enabled');
            $table->string('bank_account_holder')->nullable()->after('bank_transfer_discount_percent');
            $table->string('bank_name')->nullable()->after('bank_account_holder');
            $table->string('bank_iban', 40)->nullable()->after('bank_name');
            $table->text('bank_transfer_note')->nullable()->after('bank_iban');
            $table->boolean('card_payment_enabled')->default(false)->after('bank_transfer_note');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'bank_transfer_enabled',
                'bank_transfer_discount_percent',
                'bank_account_holder',
                'bank_name',
                'bank_iban',
                'bank_transfer_note',
                'card_payment_enabled',
            ]);
        });
    }
};
