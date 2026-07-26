<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * status kolonunu ENUM'dan VARCHAR'a çevirir.
     *
     * ENUM her yeni durum için şema değişikliği (ve MySQL'e özgü ham SQL)
     * gerektiriyordu. Tutar uyuşmazlığı tespit edilen siparişler için eklenen
     * 'review' durumu da bu yüzden kaydedilemiyordu.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
