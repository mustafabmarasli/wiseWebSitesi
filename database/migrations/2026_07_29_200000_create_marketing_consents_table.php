<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ticari elektronik ileti (pazarlama) onayları.
     *
     * KVKK'dan AYRI bir konudur: 6563 sayılı Elektronik Ticaretin
     * Düzenlenmesi Hakkında Kanun, pazarlama amaçlı e-posta/SMS/arama için
     * ÖNCEDEN AYRI ONAY ister. "Aydınlatma metnini okudum" kutusu bu onayın
     * yerine geçmez.
     *
     * Kanal başına ayrı satır tutulur çünkü İYS (İleti Yönetim Sistemi)
     * onayları kanal bazında (E-POSTA / MESAJ / ARAMA) kaydeder ve
     * her onay için tarih, kaynak ve IP ister.
     */
    public function up(): void
    {
        Schema::create('marketing_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Kişi e-posta VEYA telefonla tanımlanır; misafir müşteriler
            // hesap açmadan da onay verebiliyor.
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();

            // email | sms | call
            $table->string('channel', 10);
            // granted | revoked
            $table->string('status', 10)->default('granted');

            // İYS ispat yükümlülüğü: onayın nereden, ne zaman ve hangi IP'den
            // alındığı. Uyuşmazlıkta ispat yükü GÖNDERİCİDEDİR.
            $table->string('source', 30);
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            // İYS'ye yüklendi mi? Yüklenmemiş onaya gönderim yapılamaz.
            $table->timestamp('synced_to_iys_at')->nullable();

            // Abonelikten çıkış bağlantısındaki anahtar. Giriş yapmadan
            // çalışmalı: e-postadaki bağlantıya tıklayan kişi çoğu zaman
            // oturum açmış değildir.
            $table->string('unsubscribe_token', 64)->unique();

            $table->timestamps();

            // Aynı kişi + kanal için tek satır. MySQL NULL'ları farklı sayar,
            // bu yüzden e-posta ve telefon ayrı indekslerde.
            $table->unique(['channel', 'email']);
            $table->unique(['channel', 'phone']);
            $table->index(['channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_consents');
    }
};
