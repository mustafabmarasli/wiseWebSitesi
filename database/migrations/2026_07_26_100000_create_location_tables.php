<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * İl / ilçe / mahalle tablolarının ŞEMASI.
 *
 * Bu tablolar başlangıçta yalnızca phpMyAdmin döküm dosyalarından
 * (`php artisan locations:import`) oluşturuluyordu. Migration olmadığı için:
 *   - test veritabanında hiç oluşmuyorlardı,
 *   - `migrate:fresh` sonrası kayboluyor ve ödeme adımı kırılıyordu.
 *
 * Şema burada, VERİ ise hâlâ SQL dosyalarında. Kurulum sırası:
 *   php artisan migrate
 *   php artisan locations:import
 *
 * Tablolar zaten varsa (mevcut kurulumlar) bu migration hiçbir şey yapmaz.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('provinces')) {
            Schema::create('provinces', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('province_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('neighborhoods')) {
            Schema::create('neighborhoods', function (Blueprint $table) {
                $table->id();
                $table->foreignId('district_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('neighborhoods');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('provinces');
    }
};
