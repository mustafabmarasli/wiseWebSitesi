<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Blog / rehber yazıları.
     *
     * Amaç ürün sayısının azlığını içerikle kapatmak: rehber arama trafiği
     * getirir, yazının içinden ürüne verilen bağlantı satışa çevirir.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('cover_image')->nullable();
            $table->string('cover_alt')->nullable();
            // electronics | health | general — hangi mağaza sayfasıyla ilgili.
            $table->string('channel', 20)->default('general');
            $table->boolean('is_published')->default(false);
            // Yayın tarihi ayrı tutulur: taslak açıp ileri tarihe kurmak,
            // eski yazının tarihini korumak ancak böyle mümkün.
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            // Liste sorgusu: yayında olanlar, tarihe göre tersten.
            $table->index(['is_published', 'published_at']);
            $table->index(['channel', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
