<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Duyurular tek satırlık ayardan çıkıp kendi tablosuna taşınıyor.
     *
     * Eskiden `settings` içinde tek başlık + tek düz metin vardı. Artık
     * birden fazla duyuru kaydedilebiliyor, kanal seçilebiliyor, görsel ve
     * biçimli metin eklenebiliyor.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            // both | electronics | health
            $table->string('channel', 20)->default('both');
            $table->string('title');
            $table->longText('body')->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            // text | image_top | image_overlay
            $table->string('layout', 20)->default('text');
            // info | warning | campaign | none
            $table->string('tone', 20)->default('info');
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index(['channel', 'is_active', 'sort_order']);
        });

        // Yayındaki duyuru kaybolmasın: `settings` içindeki mevcut kayıt
        // yeni tabloya taşınır. Bu olmadan yayın sonrası duyuru aniden
        // kaybolurdu.
        $ayar = DB::table('settings')->first();

        if ($ayar && filled($ayar->announcement_text ?? null)) {
            DB::table('announcements')->insert([
                'channel'     => 'both',
                'title'       => $ayar->announcement_title ?: 'Bilgilendirme',
                'body'        => '<p>' . e($ayar->announcement_text) . '</p>',
                'layout'      => 'text',
                'tone'        => 'warning',
                'sort_order'  => 0,
                'is_active'   => (bool) ($ayar->announcement_enabled ?? false),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['announcement_enabled', 'announcement_title', 'announcement_text']);
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('announcement_enabled')->default(false);
            $table->string('announcement_title')->nullable();
            $table->text('announcement_text')->nullable();
        });

        Schema::dropIfExists('announcements');
    }
};
