<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            // email | sms
            $table->string('channel', 10);
            $table->string('title');                    // yalnızca panelde görünen ad
            $table->string('subject')->nullable();      // e-postada konu satırı
            $table->text('body');
            // draft | queued | sending | sent | failed
            $table->string('status', 12)->default('draft');

            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            // Gönderim sırasında onayını geri çekenler — atlanır, hata sayılmaz.
            $table->unsignedInteger('skipped_count')->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('last_error')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'channel']);
        });

        // Kime ne zaman gönderildiği. İspat ve "ikinci kez gönderme" kontrolü
        // için gerekli; hatalar da burada tutulur ki tekrar denenebilsin.
        Schema::create('campaign_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketing_consent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contact');                  // gönderim anındaki e-posta/telefon
            // sent | failed | skipped
            $table->string('status', 10);
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // Aynı kampanyada aynı kişiye iki kez gönderilmesin.
            $table->unique(['campaign_id', 'contact']);
            $table->index(['campaign_id', 'status']);
        });

        // Ana şalter. Varsayılan KAPALI: İYS işi bitmeden gönderim yapılmamalı,
        // kodun yayına alınmasıyla birlikte gönderim açılmamalı.
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('marketing_sending_enabled')->default(false)->after('new_customer_telegram_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('marketing_sending_enabled');
        });

        Schema::dropIfExists('campaign_deliveries');
        Schema::dropIfExists('campaigns');
    }
};
