<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Anasayfa sağ rafındaki yazı sırası panelden belirlenebilsin.
     *
     * Varsayılan 0: dokunulmayan yazılarda sıralama eskisi gibi yayın
     * tarihine göre (yeni üstte) kalır.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('channel');
            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['is_published', 'sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
