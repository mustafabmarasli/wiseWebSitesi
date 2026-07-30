<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Duyuruda arka plan ve yazı rengi panelden seçilebilsin.
     *
     * Sabit renklerle açık zeminli görsellerde yazı okunmuyordu. İkisi de
     * nullable: boş bırakılırsa yerleşime göre otomatik renk kullanılır,
     * yani mevcut duyurular olduğu gibi çalışmaya devam eder.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('bg_color', 20)->nullable()->after('layout');
            $table->string('text_color', 20)->nullable()->after('bg_color');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['bg_color', 'text_color']);
        });
    }
};
