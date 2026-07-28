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
        // Google Merchant Center akisi icin gerekli alanlar.
        Schema::table('products', function (Blueprint $table) {
            // Marka. Google, GTIN yoksa marka + MPN ister; MPN olarak urun id'si
            // kullanilir. Marka bos birakilirsa akisa identifier_exists=no yazilir.
            $table->string('brand')->nullable()->after('name');

            // Uretici barkodu. Varsa markadan daha guclu bir tanimlayicidir.
            $table->string('gtin', 50)->nullable()->after('brand');
        });

        Schema::table('categories', function (Blueprint $table) {
            // Google urun taksonomisi kimligi (orn. 3853 = Elektronik > Bilesenler).
            // Bos birakilabilir; Google kendi tahmin eder ama dogru deger
            // reklam eslesmesini belirgin sekilde iyilestirir.
            $table->string('google_product_category', 20)->nullable()->after('channel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['brand', 'gtin']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('google_product_category');
        });
    }
};
