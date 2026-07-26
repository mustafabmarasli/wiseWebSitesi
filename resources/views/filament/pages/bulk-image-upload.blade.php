<x-filament-panels::page>

    {{-- Filament'in derlenmis CSS'i rastgele Tailwind siniflarini icermez;
         bu sayfaya ozel stiller burada tanimlanir. --}}
    <style>
        .tg-gizli { display: none !important; }
        .tg-birak {
            border: 2px dashed rgb(209 213 219);
            border-radius: 0.75rem;
            padding: 2rem 1rem;
            text-align: center;
            transition: background-color .15s, border-color .15s;
        }
        .dark .tg-birak { border-color: rgb(55 65 81); }
        .tg-birak.tg-aktif { border-color: rgb(59 130 246); background: rgba(59, 130, 246, .06); }
        .tg-ikon { width: 2.5rem; height: 2.5rem; margin: 0 auto; color: rgb(156 163 175); }
        .tg-baglanti {
            font-weight: 600; color: rgb(37 99 235); cursor: pointer;
            background: none; border: 0; padding: 0; font-size: inherit;
        }
        .tg-baglanti:hover { text-decoration: underline; }
        .tg-ipucu { font-size: .75rem; color: rgb(107 114 128); margin-top: .25rem; }
        .tg-metin { font-size: .875rem; color: rgb(75 85 99); margin-top: .75rem; }
        .dark .tg-metin { color: rgb(156 163 175); }

        .tg-cubuk-dis {
            height: .5rem; background: rgb(229 231 235);
            border-radius: 9999px; overflow: hidden;
        }
        .dark .tg-cubuk-dis { background: rgb(55 65 81); }
        .tg-cubuk-ic { height: 100%; background: rgb(37 99 235); transition: width .2s; }

        .tg-izgara {
            display: grid; gap: .5rem; max-height: 16rem; overflow-y: auto;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        @media (min-width: 640px) { .tg-izgara { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .tg-izgara { grid-template-columns: repeat(8, minmax(0, 1fr)); } }

        .tg-kucuk-resim {
            width: 100%; aspect-ratio: 1 / 1; object-fit: cover;
            border-radius: .5rem; border: 1px solid rgb(229 231 235);
        }
        .dark .tg-kucuk-resim { border-color: rgb(55 65 81); }
        .tg-dosya-adi {
            font-size: .625rem; color: rgb(107 114 128); margin-top: .25rem;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }

        .tg-satir { display: flex; align-items: flex-start; gap: .625rem; font-size: .875rem; }
        .tg-satir + .tg-satir { margin-top: .375rem; }
        .tg-rozet { width: 1.25rem; height: 1.25rem; flex-shrink: 0; }
        .tg-ok { color: rgb(34 197 94); }
        .tg-hata { color: rgb(239 68 68); }

        .tg-arasi > * + * { margin-top: 1.5rem; }
        .tg-ust { margin-top: 1rem; }
        .tg-sag { display: flex; justify-content: flex-end; }
        .tg-aralik { display: flex; align-items: flex-start; gap: .75rem; cursor: pointer; }
        .tg-ozet {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: .75rem; font-size: .875rem;
        }
    </style>

    <div x-data="topluGorsel()" class="tg-arasi">

        {{-- Ayarlar --}}
        <x-filament::section>
            <x-slot name="heading">Nasıl yüklensin?</x-slot>

            <label class="tg-aralik">
                <input type="checkbox" wire:model="asGallery" style="margin-top:.25rem">
                <span style="font-size:.875rem">
                    <strong>Galeriye ekle</strong>
                    <span style="display:block;color:rgb(107 114 128)">
                        İşaretlemezsen görseller <strong>ana görsel</strong> olarak ayarlanır (varsa üzerine yazar).
                        İşaretlersen ürünün galerisine eklenir.
                    </span>
                </span>
            </label>
        </x-filament::section>

        {{-- Yükleme alanı --}}
        <x-filament::section>
            <x-slot name="heading">Görselleri seçin</x-slot>
            <x-slot name="description">
                Dosya adı ürünün URL adresiyle aynı olmalı. Örnek: <code>esp32-devkit.jpg</code> →
                <code>/urun/esp32-devkit</code>. Galeri için <code>esp32-devkit-2.jpg</code> gibi numaralandırabilirsiniz.
            </x-slot>

            <div
                class="tg-birak"
                :class="suruklendi && 'tg-aktif'"
                @dragover.prevent="suruklendi = true"
                @dragleave.prevent="suruklendi = false"
                @drop.prevent="suruklendi = false; dosyalariIsle($event.dataTransfer.files)"
            >
                <input type="file" multiple accept="image/*" x-ref="secici" class="tg-gizli"
                       @change="dosyalariIsle($event.target.files)">

                <svg class="tg-ikon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 7.5 7.5 12M12 7.5v12" />
                </svg>

                <p class="tg-metin">
                    Görselleri buraya sürükleyin veya
                    <button type="button" class="tg-baglanti" @click="$refs.secici.click()">dosya seçin</button>
                </p>
                <p class="tg-ipucu">JPG, PNG, WebP · birden fazla seçebilirsiniz</p>
            </div>

            {{-- Dönüştürme ilerlemesi --}}
            <div x-show="donusturuluyor" x-cloak class="tg-ust">
                <div class="tg-ozet">
                    <span style="color:rgb(107 114 128)">Görseller optimize ediliyor...</span>
                    <span style="font-weight:500" x-text="`${islenen} / ${toplam}`"></span>
                </div>
                <div class="tg-cubuk-dis">
                    <div class="tg-cubuk-ic" :style="`width: ${toplam ? (islenen / toplam * 100) : 0}%`"></div>
                </div>
            </div>

            {{-- Hazırlanan dosyalar --}}
            <div x-show="hazir.length > 0 && !donusturuluyor" x-cloak class="tg-ust">
                <div class="tg-ozet">
                    <span>
                        <strong x-text="hazir.length"></strong> görsel hazır
                        <span style="color:rgb(107 114 128)" x-text="`(%${kazanc} küçüldü)`"></span>
                    </span>
                    <button type="button" class="tg-baglanti" style="color:rgb(107 114 128)"
                            @click="temizle()">Temizle</button>
                </div>

                <div class="tg-izgara">
                    <template x-for="(d, i) in hazir" :key="i">
                        <div>
                            <img :src="d.onizleme" class="tg-kucuk-resim" alt="">
                            <p class="tg-dosya-adi" :title="d.ad" x-text="d.ad"></p>
                        </div>
                    </template>
                </div>
            </div>
        </x-filament::section>

        {{-- Kaydet --}}
        <div x-show="hazir.length > 0" x-cloak class="tg-sag">
            <x-filament::button wire:click="save" wire:loading.attr="disabled" size="lg">
                <span wire:loading.remove wire:target="save">
                    Yükle (<span x-text="hazir.length"></span> görsel)
                </span>
                <span wire:loading wire:target="save">Yükleniyor...</span>
            </x-filament::button>
        </div>

        {{-- Sonuçlar --}}
        @if (!empty($results))
            <x-filament::section>
                <x-slot name="heading">Sonuç</x-slot>

                @foreach ($results as $r)
                    <div class="tg-satir">
                        @if ($r['status'] === 'ok')
                            <x-filament::icon icon="heroicon-m-check-circle" class="tg-rozet tg-ok" />
                        @else
                            <x-filament::icon icon="heroicon-m-x-circle" class="tg-rozet tg-hata" />
                        @endif
                        <div style="min-width:0">
                            <strong>{{ $r['name'] }}</strong>
                            <span style="color:rgb(107 114 128)">— {{ $r['message'] }}</span>
                        </div>
                    </div>
                @endforeach
            </x-filament::section>
        @endif
    </div>

    @script
    <script>
        Alpine.data('topluGorsel', () => ({
            suruklendi: false,
            donusturuluyor: false,
            islenen: 0,
            toplam: 0,
            hazir: [],
            hamBoyut: 0,
            yeniBoyut: 0,

            get kazanc() {
                if (!this.hamBoyut) return 0;
                return Math.max(0, Math.round((1 - this.yeniBoyut / this.hamBoyut) * 100));
            },

            async dosyalariIsle(fileList) {
                const dosyalar = Array.from(fileList).filter(f => f.type.startsWith('image/'));
                if (!dosyalar.length) return;

                this.donusturuluyor = true;
                this.islenen = 0;
                this.toplam = dosyalar.length;

                const donusenler = [];

                for (const dosya of dosyalar) {
                    try {
                        const blob = await this.webpYap(dosya);
                        this.hamBoyut += dosya.size;
                        this.yeniBoyut += blob.size;

                        const ad = dosya.name.replace(/\.[^.]+$/, '') + '.webp';
                        donusenler.push(new File([blob], ad, { type: 'image/webp' }));

                        this.hazir.push({ ad, onizleme: URL.createObjectURL(blob) });
                    } catch (e) {
                        console.error('Dönüştürülemedi:', dosya.name, e);
                    }

                    this.islenen++;
                }

                // Livewire'a yalnizca donusturulmus dosyalar gonderilir
                if (donusenler.length) {
                    await this.$wire.uploadMultiple('files', donusenler, () => {}, () => {}, () => {});
                }

                this.donusturuluyor = false;
            },

            /**
             * Görseli tarayıcıda WebP'ye çevirir ve en uzun kenarı 1200px'e indirir.
             * Sunucuda GD/Imagick gerekmez, yükleme de çok daha hızlı olur.
             */
            webpYap(dosya) {
                return new Promise((resolve, reject) => {
                    const okuyucu = new FileReader();

                    okuyucu.onerror = () => reject(new Error('Dosya okunamadı'));
                    okuyucu.onload = () => {
                        const img = new Image();

                        img.onerror = () => reject(new Error('Görsel çözümlenemedi'));
                        img.onload = () => {
                            const ENBOY = 1200;
                            let g = img.width, y = img.height;

                            if (g > ENBOY || y > ENBOY) {
                                const oran = Math.min(ENBOY / g, ENBOY / y);
                                g = Math.round(g * oran);
                                y = Math.round(y * oran);
                            }

                            const canvas = document.createElement('canvas');
                            canvas.width = g;
                            canvas.height = y;

                            const ctx = canvas.getContext('2d');
                            // Şeffaf PNG'ler WebP'de siyah çıkmasın diye beyaz zemin
                            ctx.fillStyle = '#FFFFFF';
                            ctx.fillRect(0, 0, g, y);
                            ctx.drawImage(img, 0, 0, g, y);

                            canvas.toBlob(
                                blob => blob ? resolve(blob) : reject(new Error('WebP üretilemedi')),
                                'image/webp',
                                0.85
                            );
                        };

                        img.src = okuyucu.result;
                    };

                    okuyucu.readAsDataURL(dosya);
                });
            },

            temizle() {
                this.hazir.forEach(d => URL.revokeObjectURL(d.onizleme));
                this.hazir = [];
                this.hamBoyut = 0;
                this.yeniBoyut = 0;
                this.$wire.set('files', []);
            },
        }));
    </script>
    @endscript

</x-filament-panels::page>
