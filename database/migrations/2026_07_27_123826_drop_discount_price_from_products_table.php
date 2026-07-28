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
        // `discount_price` hiçbir yerde okunmuyordu ve dolu olduğu 17 üründe
        // değeri `price` ile birebir aynıydı (seeder her ikisini de yazıyordu).
        // Sitenin indirim mekanizması `eski_fiyat` ↔ `price` karşılaştırması;
        // ikinci bir fiyat alanı tutmak yalnızca "hangisi geçerli?" belirsizliği
        // yaratıyordu. Veri kaybı yok, kolon tekrar veriden ibaretti.
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('discount_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('discount_price', 10, 2)->nullable()->after('price');
        });
    }
};
