<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kayitli adresler de siparisler gibi il/ilce/mahalle tutsun; boylece
        // odeme formunda kayitli adres secilince alanlar otomatik dolabilir.
        Schema::table('addresses', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->after('city')->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->after('province_id')->constrained()->nullOnDelete();
            $table->foreignId('neighborhood_id')->nullable()->after('district_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['neighborhood_id']);
            $table->dropColumn(['province_id', 'district_id', 'neighborhood_id']);
        });
    }
};
