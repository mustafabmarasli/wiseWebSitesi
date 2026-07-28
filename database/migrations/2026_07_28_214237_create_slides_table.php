<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 20);                 // electronics | health
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('badge')->nullable();           // üstteki küçük etiket
            $table->string('badge_color', 20)->default('trendyol');
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->string('primary_text')->nullable();
            $table->string('primary_url')->nullable();
            $table->string('secondary_text')->nullable();
            $table->string('secondary_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['channel', 'is_active', 'sort_order']);
        });

        // Halihazirda kodda gomulu olan slaytlar veritabanina tasinir; boylece
        // yayindan sonra sayfa birebir ayni gorunur ve panelden duzenlenebilir.
        $simdi   = now();
        $slaytlar = [
            // Elektronik
            ['electronics', 'images/banner.png',  'Elektronik Geliştirme Kartları Kampanyası', 'Gelişmiş Donanım', 'trendyol',
             'Mikroişlemci Geliştirme Kartları', 'ESP32, ESP8266, S Tipi LED ve COB LED aydınlatma bileşenleri stoktan teslim.',
             'Şimdi Keşfet', '#tum-urunler', 'Destek Al', '/iletisim', 1],
            ['electronics', 'images/banner2.png', 'COB LED Aydınlatma Teknolojisi', 'Yeni Nesil Işık', 'emerald',
             'COB & S-Tipi LED Teknolojisi', 'Noktasız kesintisiz ışık veren şerit LED\'ler ve bükülebilir S-Tipi aydınlatmalar.',
             'Ürünleri Gör', '#tum-urunler', 'Bilgi Al', '/iletisim', 2],
            ['electronics', 'images/banner3.png', 'IoT Projeleri', 'Geleceğin Teknolojisi', 'blue',
             'IoT Projenize Özel Çözüm', 'Akıllı ev sistemleri, giyilebilir teknoloji ve sensör modülleriyle projeler üretin.',
             'Projelere Başla', '#tum-urunler', 'İletişime Geç', '/iletisim', 3],

            // Sağlık
            ['health', 'images/banner_health.png',  'Genel Sağlık Kampanyası', 'Sağlık & Medikal', 'trendyol',
             'Kontakt Lens Aksesuarları', 'Yetkili satıcısı olduğumuz orijinal DMV® vantuzları ve sızdırmaz lens kapları.',
             'Ürünleri Gör', '#tum-urunler', 'Danışın', '/iletisim', 1],
            ['health', 'images/banner_health2.png', 'Orijinal DMV Aparatları', 'Amerika\'dan İthal', 'amber',
             'Orijinal DMV® Ürünleri', 'Skleral, sert ve yumuşak lensler için patentli takma ve çıkarma vantuzları.',
             'Şimdi İncele', '#tum-urunler', 'Bilgi Al', '/iletisim', 2],
        ];

        foreach ($slaytlar as $s) {
            DB::table('slides')->insert([
                'channel'        => $s[0],
                'image_path'     => $s[1],
                'image_alt'      => $s[2],
                'badge'          => $s[3],
                'badge_color'    => $s[4],
                'title'          => $s[5],
                'subtitle'       => $s[6],
                'primary_text'   => $s[7],
                'primary_url'    => $s[8],
                'secondary_text' => $s[9],
                'secondary_url'  => $s[10],
                'sort_order'     => $s[11],
                'is_active'      => true,
                'created_at'     => $simdi,
                'updated_at'     => $simdi,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};
