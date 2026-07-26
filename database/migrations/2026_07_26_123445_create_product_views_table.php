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
        // Ham görüntülenme kayıtları — zaman bazlı grafikler için.
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_hash', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('created_at');
            $table->index(['product_id', 'created_at']);
        });

        // Hızlı sıralama için ürün üzerinde denormalize sayaç.
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('view_count')->default(0)->after('satis_sayisi');
            $table->index('view_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['view_count']);
            $table->dropColumn('view_count');
        });

        Schema::dropIfExists('product_views');
    }
};
