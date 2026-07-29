<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Stoğu biten ürün için "gelince haber ver" kayıtları.
     */
    public function up(): void
    {
        Schema::create('stock_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // Üye kaydı silinse de bildirim isteği e-posta üzerinden ayakta kalır.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            // Aynı kişi aynı ürün için ikinci kayıt açamaz; tekrar tıklamak
            // mevcut kaydı tazeler (bkz. StockNotificationController::store).
            $table->unique(['product_id', 'email']);
            // "Bu ürünü kaç kişi bekliyor" sorgusu bu indeksten yürür.
            $table->index(['product_id', 'notified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_notifications');
    }
};
