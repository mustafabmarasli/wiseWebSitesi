<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Müşteri bilgileri
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 20);
            $table->text('address');
            $table->string('city', 100);
            $table->string('zip_code', 20)->nullable();
            $table->string('identity_number', 11)->nullable(); // iyzico zorunlu (TC/pasaport)

            // Ödeme bilgileri
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 5)->default('TRY');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');

            // iyzico izleme alanları
            $table->string('iyzico_token')->nullable()->index();
            $table->string('iyzico_payment_id')->nullable();
            $table->string('iyzico_conversation_id')->nullable()->index();
            $table->string('iyzico_payment_status')->nullable();

            // Sepet snapshot (sipariş anındaki ürünler)
            $table->json('cart_snapshot')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
