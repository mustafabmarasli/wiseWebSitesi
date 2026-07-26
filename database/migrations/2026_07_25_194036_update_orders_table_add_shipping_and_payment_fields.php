<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->default('iyzico')->after('identity_number');
            $table->string('shipping_method')->nullable()->default('Standart Kargo')->after('payment_method');
            $table->decimal('shipping_cost', 10, 2)->default(0.00)->after('shipping_method');
            $table->timestamp('estimated_delivery_at')->nullable()->after('shipping_cost');
        });

        // Modifying status column to include shipped, delivered, and cancelled states.
        // MODIFY COLUMN / ENUM yalnızca MySQL sözdizimidir; SQLite üzerinde çalışan
        // testlerde bu adım atlanır (sonraki migration kolonu zaten string'e çevirir).
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'shipped', 'delivered', 'failed', 'refunded', 'cancelled') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['estimated_delivery_at', 'shipping_cost', 'shipping_method', 'payment_method']);
        });

        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending'");
        }
    }
};
