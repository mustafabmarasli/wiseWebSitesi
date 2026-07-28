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
        // "Danışmanlık ve Dış Ticaret" bölümü panelden gizlenebilsin.
        // Varsayilan false: istek uzerine kapali baslar.
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('consulting_enabled')->default(false)->after('announcement_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('consulting_enabled');
        });
    }
};
