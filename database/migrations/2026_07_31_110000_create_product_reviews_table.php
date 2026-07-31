<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gerçek müşteri yorumları.
     *
     * `Product.rating` alanı (seed veri) buradan ETKİLENMEZ — seed veri hâlâ
     * ayrı bir alan. Bu tablo yalnızca "gerçekten satın almış" müşterilerden
     * gelen yorumları tutar; ürün detay sayfasında ikisi net şekilde ayrı
     * gösterilir, aggregateRating şeması yalnızca BU tablodaki onaylı
     * yorumlar için basılır.
     */
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Yorumun hangi siparişten hak kazandığının kaydı — ispat/denetim
            // içindir. O sipariş silinirse yorum da silinir (nadir, kabul
            // edilebilir): satın alma kanıtı olmadan yorum tek başına anlamsız.
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('rating');
            $table->text('comment');

            // pending | approved | rejected — yayına girmeden önce onay
            // gerekir. Denetimsiz herkese açık yorum, küçük bir işletme için
            // hem spam hem itibar riski.
            $table->string('status', 10)->default('pending');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            // Bir müşteri bir ürüne yalnızca BİR yorum yazabilir — kaç kez
            // satın almış olursa olsun. Tekrar yazmak isterse mevcut panelden
            // yönetici tarafından güncellenebilir (şimdilik yalnızca onay/red).
            $table->unique(['product_id', 'user_id']);
            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
