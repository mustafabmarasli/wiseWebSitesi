<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Farklı fatura adresi
            $table->text('billing_address')->nullable()->after('zip_code');
            $table->string('billing_city', 100)->nullable()->after('billing_address');

            // Ticari fatura alanları
            $table->boolean('is_corporate')->default(false)->after('billing_city');
            $table->string('company_name')->nullable()->after('is_corporate');
            $table->string('tax_number', 10)->nullable()->after('company_name');
            $table->string('tax_office', 100)->nullable()->after('tax_number');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'billing_address', 'billing_city',
                'is_corporate', 'company_name', 'tax_number', 'tax_office',
            ]);
        });
    }
};
