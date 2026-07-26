<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TC Kimlik numaralarını şifreler (KVKK).
 *
 * DİKKAT: Şifreleme APP_KEY'e bağlıdır. APP_KEY değiştirilirse mevcut kimlik
 * numaraları BİR DAHA OKUNAMAZ. Anahtarı yedekleyin ve asla değiştirmeyin.
 *
 * Not: Eloquent yerine bilinçli olarak DB facade kullanılıyor; model üzerindeki
 * 'encrypted' cast'i devreye girip değerleri iki kez şifrelemesin diye.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Şifreli metin 11 karaktere sığmaz (~190 karakter)
        Schema::table('orders', function (Blueprint $table) {
            $table->text('identity_number')->nullable()->change();
        });

        DB::table('orders')
            ->whereNotNull('identity_number')
            ->where('identity_number', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($orders) {
                foreach ($orders as $order) {
                    // Zaten şifrelenmişse tekrar şifreleme (kısmi çalışmaya karşı)
                    if (self::isEncrypted($order->identity_number)) {
                        continue;
                    }

                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['identity_number' => Crypt::encryptString($order->identity_number)]);
                }
            });
    }

    public function down(): void
    {
        DB::table('orders')
            ->whereNotNull('identity_number')
            ->where('identity_number', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($orders) {
                foreach ($orders as $order) {
                    if (!self::isEncrypted($order->identity_number)) {
                        continue;
                    }

                    DB::table('orders')
                        ->where('id', $order->id)
                        ->update(['identity_number' => Crypt::decryptString($order->identity_number)]);
                }
            });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('identity_number', 11)->nullable()->change();
        });
    }

    private static function isEncrypted(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        try {
            Crypt::decryptString($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
};
