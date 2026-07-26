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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('neighborhood_id')->nullable();

            $table->unsignedBigInteger('billing_province_id')->nullable();
            $table->unsignedBigInteger('billing_district_id')->nullable();
            $table->unsignedBigInteger('billing_neighborhood_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'province_id', 'district_id', 'neighborhood_id',
                'billing_province_id', 'billing_district_id', 'billing_neighborhood_id'
            ]);
        });
    }
};
