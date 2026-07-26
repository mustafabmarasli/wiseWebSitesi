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
        // Duyuru penceresi panelden yonetilebilsin; satisa baslayinca
        // kod degistirmeden kapatilabilmeli.
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('announcement_enabled')->default(false)->after('free_shipping_threshold');
            $table->string('announcement_title')->nullable()->after('announcement_enabled');
            $table->text('announcement_text')->nullable()->after('announcement_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['announcement_enabled', 'announcement_title', 'announcement_text']);
        });
    }
};
