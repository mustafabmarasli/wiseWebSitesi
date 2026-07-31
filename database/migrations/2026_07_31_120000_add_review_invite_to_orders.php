<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sipariş "Teslim Edildi"ye ilk geçtiğinde bir kez yorum daveti e-postası
     * gider. Bu alan olmadan sipariş her düzenlendiğinde tekrar giderdi
     * (bkz. shipped_notified_at ile aynı mantık).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('review_invite_sent_at')->nullable()->after('shipped_notified_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('review_invite_sent_at');
        });
    }
};
