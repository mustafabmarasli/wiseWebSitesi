<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tek satırlık site ayarları tablosu.
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Standart kargo ücreti (TL). 0 = kargo daima ücretsiz.
            $table->decimal('standard_shipping_cost', 10, 2)->default(0);

            // Bu tutarın üzerindeki siparişlerde kargo ücretsiz.
            // null = ücretsiz kargo kampanyası kapalı.
            $table->decimal('free_shipping_threshold', 10, 2)->nullable();

            $table->timestamps();
        });

        // Varsayılan satırı oluştur
        DB::table('settings')->insert([
            'standard_shipping_cost'  => 0,
            'free_shipping_threshold' => null,
            'created_at'              => now(),
            'updated_at'              => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
