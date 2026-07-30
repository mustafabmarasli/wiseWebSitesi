{{--
    Duyuru penceresi. İçerik `announcements` tablosundan gelir (Panel →
    Duyurular).

    ÇOKLU DUYURU: yayında birden fazla duyuru varsa SIRAYLA gösterilir —
    ziyaretçi birincisini kapatınca ikincisi açılır. Hepsini aynı anda basmak
    üst üste binen pencereler demek olurdu.

    ÖNEMLİ: Bu parça `@section('modals')` içine konur, `content`'e DEĞİL.
    <main> öğesindeki `animate-fade-in` bir `transform` üretiyor ve içindeki
    position:fixed öğeleri viewport yerine <main>'e göre konumlandırıyor.
--}}
@php
    $duyurular = \App\Models\Announcement::queueForChannel($channel ?? 'electronics');

    // Anahtar: kimlik + son değişiklik zamanı. Metni güncelleyince pencere
    // yeniden açılır; aynı kalırsa kapatan ziyaretçiye tekrar gösterilmez.
    $anahtarlar = $duyurular
        ->map(fn ($d) => 'duyuru_' . $d->id . '_' . $d->updated_at?->timestamp)
        ->values()
        ->all();
@endphp

@if ($duyurular->isNotEmpty())
    <div
        x-data="{
            anahtarlar: @js($anahtarlar),
            aktif: null,
            gorunur: false,

            init() {
                this.aktif = this.siradaki(-1);

                if (this.aktif !== null) {
                    setTimeout(() => { this.gorunur = true }, 350);
                }
            },

            /** Verilen sıradan SONRA gelen, kapatılmamış ilk duyurunun sırası. */
            siradaki(sira) {
                const kapatilanlar = this.kapatilanlar();

                for (let i = sira + 1; i < this.anahtarlar.length; i++) {
                    if (! kapatilanlar.includes(this.anahtarlar[i])) {
                        return i;
                    }
                }

                return null;
            },

            kapatilanlar() {
                try {
                    return JSON.parse(sessionStorage.getItem('duyuru_kapatilanlar') || '[]');
                } catch (e) {
                    return [];
                }
            },

            kapat() {
                const kapatilanlar = this.kapatilanlar();
                kapatilanlar.push(this.anahtarlar[this.aktif]);
                sessionStorage.setItem('duyuru_kapatilanlar', JSON.stringify(kapatilanlar));

                const sonraki = this.siradaki(this.aktif);

                if (sonraki === null) {
                    this.gorunur = false;
                    this.aktif = null;
                    return;
                }

                // Kısa bir ara: pencere anında değişince ziyaretçi kapatma
                // tıklamasının işlenmediğini sanıp tekrar tıklıyor.
                this.gorunur = false;
                setTimeout(() => {
                    this.aktif = sonraki;
                    this.gorunur = true;
                }, 220);
            },
        }"
        x-show="gorunur"
        x-transition.opacity.duration.250ms
        class="duyuru-katman"
        style="display:none"
        role="dialog"
        aria-modal="true"
        aria-labelledby="duyuru-baslik"
        @keydown.escape.window="kapat()"
    >
        {{-- Arka plan --}}
        <div @click="kapat()"
             style="position:absolute; inset:0; background:rgba(2,6,23,.62); backdrop-filter:blur(4px);"></div>

        @foreach ($duyurular as $sira => $duyuru)
            @php
                $ton    = $duyuru->tone_style;
                $ortuk  = $duyuru->isOverlay();
                $altta  = $duyuru->imageBelowText();
                // Renkler panelden gelir; seçilmemişse yerleşime göre
                // otomatik (koyu zeminde beyaz yazı, açık zeminde koyu).
                $zemin  = $duyuru->bg_color_value;
                $yazi   = $duyuru->text_color_value;
                $govde  = $duyuru->body_color_value;
            @endphp

            {{-- Kart. Tümü basılır, yalnızca sırası gelen gösterilir. --}}
            {{-- `color` kartın kendisinde: sayaç, kapatma yazısı ve zengin
                 metin bunu miras alır, böylece koyu zeminde hiçbir yazı
                 sabit gri kalıp kaybolmaz. --}}
            <div class="duyuru-kart" x-show="aktif === {{ $sira }}"
                 style="display:none; background:{{ $ortuk ? '#fff' : $zemin }}; color:{{ $govde }};">

                @unless ($ortuk)
                    <div style="height:6px; background:linear-gradient(90deg,#1B4A7A,#2DD4BF,#F59E0B);"></div>
                @endunless

                {{-- Kapat. Görselin üzerindeyken beyaz, aksi hâlde gri. --}}
                <button type="button" @click="kapat()" aria-label="Duyuruyu kapat"
                        class="duyuru-kapat {{ $ortuk ? 'duyuru-kapat-acik' : '' }}" title="Kapat">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                @if ($ortuk)
                    {{-- Yazı görselin üzerinde: yazının okunabilmesi için görselin
                         üstüne seçilen renkten bir perde serilir. Perde olmadan
                         açık zeminli görselde yazı kayboluyor. --}}
                    <div class="duyuru-ortuk">
                        <img src="{{ $duyuru->image_url }}" alt="{{ $duyuru->image_alt ?? '' }}" class="duyuru-ortuk-gorsel">
                        <div class="duyuru-ortuk-perde"
                             style="background:linear-gradient(to top, {{ $zemin }} 0%, {{ $zemin }}D9 55%, {{ $zemin }}59 100%);"></div>
                        <div class="duyuru-ortuk-metin">
                            <h2 @if ($sira === 0) id="duyuru-baslik" @endif class="duyuru-baslik-acik" style="color:{{ $yazi }};">{{ $duyuru->title }}</h2>
                            @if (filled($duyuru->body))
                                <div class="duyuru-govde duyuru-govde-acik" style="color:{{ $govde }};">{!! $duyuru->body !!}</div>
                            @endif
                        </div>
                    </div>

                    <div style="padding:1.25rem 2rem 2rem; text-align:center;">
                        @include('partials.announcement_actions', [
                            'duyuru'      => $duyuru,
                            'sonKuyrukta' => $sira === $duyurular->count() - 1,
                        ])
                    </div>
                @else
                    {{-- Görsel metnin üstünde mi altında mı: panelden seçilir. --}}
                    @if ($duyuru->usesImage() && ! $altta)
                        <img src="{{ $duyuru->image_url }}" alt="{{ $duyuru->image_alt ?? '' }}" class="duyuru-gorsel">
                    @endif

                    <div style="padding:{{ $duyuru->usesImage() && ! $altta ? '1.75rem' : '2.5rem' }} 2rem {{ $altta ? '1.75rem' : '2rem' }}; text-align:center;">
                        @if ($ton)
                            <div class="duyuru-simge" style="background:{{ $ton['zemin'] }}; color:{{ $ton['renk'] }};">
                                <span class="duyuru-dalga" style="background:{{ $ton['renk'] }};" aria-hidden="true"></span>
                                <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                     stroke-width="1.8" style="position:relative; z-index:1;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ton['ikon'] }}" />
                                </svg>
                            </div>
                        @endif

                        <h2 @if ($sira === 0) id="duyuru-baslik" @endif class="duyuru-baslik" style="color:{{ $yazi }};">{{ $duyuru->title }}</h2>

                        @if (filled($duyuru->body))
                            <div class="duyuru-govde" style="color:{{ $govde }};">{!! $duyuru->body !!}</div>
                        @endif

                        @include('partials.announcement_actions', [
                            'duyuru'      => $duyuru,
                            'sonKuyrukta' => $sira === $duyurular->count() - 1,
                        ])
                    </div>

                    @if ($altta)
                        <img src="{{ $duyuru->image_url }}" alt="{{ $duyuru->image_alt ?? '' }}" class="duyuru-gorsel">
                    @endif
                @endif

                {{-- Kuyrukta kaçıncı olduğunu göster: kapatınca yeni bir pencere
                     açılması sürpriz olmasın. --}}
                @if ($duyurular->count() > 1)
                    <p class="duyuru-sayac">{{ $sira + 1 }} / {{ $duyurular->count() }} duyuru</p>
                @endif
            </div>
        @endforeach
    </div>

    <style>
        /* Yerleşim CSS'te: x-show yalnızca display'i yönetsin.
           Alpine gizlerken inline display:none yazar, gösterirken kaldırır;
           o an bu sınıftaki display:flex devreye girer. */
        .duyuru-katman {
            position: fixed; inset: 0; z-index: 10000;
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
        }

        .duyuru-kart {
            position: relative; width: 100%; max-width: 32rem;
            background: #fff; border-radius: 1.5rem;
            box-shadow: 0 25px 60px rgba(0,0,0,.35);
            overflow: hidden;
            /* Uzun metin taşarsa kart içinde kaydırılır; yoksa uzun bir
               misyon metni ekranın dışına çıkıyor ve buton görünmüyor. */
            max-height: 90vh; overflow-y: auto;
        }

        /* Görsel üstte yerleşimi */
        .duyuru-gorsel { display: block; width: 100%; height: 12rem; object-fit: cover; }

        /* Yazı görselin üzerinde yerleşimi */
        .duyuru-ortuk { position: relative; }
        .duyuru-ortuk-gorsel { display: block; width: 100%; height: 16rem; object-fit: cover; }
        /* Perdenin rengi panelden seçilir, inline stille basılır. */
        .duyuru-ortuk-perde { position: absolute; inset: 0; }
        .duyuru-ortuk-metin {
            position: absolute; inset: auto 0 0 0; padding: 1.75rem 2rem;
            text-align: center;
        }
        .duyuru-baslik-acik {
            font-size: 1.5rem; font-weight: 900;
            letter-spacing: -.02em; margin: 0;
            text-shadow: 0 2px 12px rgba(0,0,0,.5);
        }
        .duyuru-govde-acik { text-align: center; }

        .duyuru-simge {
            position: relative; width: 4rem; height: 4rem; margin: 0 auto 1.25rem;
            border-radius: 1rem;
            display: flex; align-items: center; justify-content: center;
        }
        .duyuru-dalga {
            position: absolute; inset: 0; border-radius: 1rem;
            opacity: .2;
            animation: duyuru-ping 1.8s cubic-bezier(0, 0, .2, 1) infinite;
        }
        @keyframes duyuru-ping {
            75%, 100% { transform: scale(1.35); opacity: 0; }
        }
        /* Hareketi azalt tercihi olan ziyaretçilerde animasyon çalışmaz */
        @media (prefers-reduced-motion: reduce) {
            .duyuru-dalga { animation: none; }
        }

        .duyuru-baslik {
            font-size: 1.5rem; font-weight: 900;
            letter-spacing: -.02em; margin: 0;
        }

        /* Zengin metin: panelden liste, kalın yazı ve bağlantı gelebilir. */
        .duyuru-govde {
            margin: .75rem auto 0; max-width: 26rem;
            font-size: .9375rem; line-height: 1.6; font-weight: 500;
            text-align: left;
        }
        .duyuru-govde p { margin: 0 0 .625rem; }
        .duyuru-govde p:last-child { margin-bottom: 0; }
        .duyuru-govde ul, .duyuru-govde ol { margin: 0 0 .625rem 1.25rem; }
        .duyuru-govde ul { list-style: disc; }
        .duyuru-govde ol { list-style: decimal; }
        .duyuru-govde li { margin-bottom: .25rem; }
        .duyuru-govde strong { font-weight: 800; color: inherit; }
        .duyuru-govde a { color: inherit; font-weight: 700; text-decoration: underline; }
        .duyuru-govde img { max-width: 100%; height: auto; border-radius: .5rem; margin: .5rem 0; }

        .duyuru-kapat {
            position: absolute; top: 1rem; right: 1rem; z-index: 2;
            width: 2.25rem; height: 2.25rem; border: 0; border-radius: 9999px;
            display: flex; align-items: center; justify-content: center;
            background: transparent; color: inherit; opacity: .55; cursor: pointer;
            transition: opacity .15s, transform .1s;
        }
        .duyuru-kapat:hover { opacity: 1; }
        .duyuru-kapat:active { transform: scale(.94); }
        /* Görsel üzerinde gri simge kayboluyor. */
        .duyuru-kapat-acik { background: rgba(2,6,23,.45); color: #fff; }
        .duyuru-kapat-acik:hover { background: rgba(2,6,23,.7); color: #fff; }

        .duyuru-butonlar {
            margin-top: 1.75rem;
            display: flex; flex-direction: column; align-items: center; gap: .625rem;
        }

        .duyuru-tamam {
            min-width: 200px;
            background: #1B4A7A; color: #fff; border: 0;
            font-weight: 800; font-size: .875rem;
            padding: .875rem 2rem; border-radius: .75rem; cursor: pointer;
            box-shadow: 0 4px 12px rgba(27,74,122,.25);
            transition: background-color .15s, transform .1s, box-shadow .15s;
            text-decoration: none; display: inline-block; text-align: center;
        }
        .duyuru-tamam:hover { background: #14385C; box-shadow: 0 6px 18px rgba(27,74,122,.35); }
        .duyuru-tamam:active { transform: scale(.98); }

        /* Bağlantı butonu varken "Anladım" ikincil hâle gelir: asıl eylem
           artık ziyaretçiyi bir yere götüren butondur. */
        .duyuru-kapat-yazi {
            background: transparent; border: 0; cursor: pointer;
            font-size: .8125rem; font-weight: 700; color: inherit; opacity: .75;
            padding: .375rem .75rem; border-radius: .5rem;
        }
        .duyuru-kapat-yazi:hover { opacity: 1; }

        .duyuru-sayac {
            padding: 0 2rem 1.25rem; margin: 0; text-align: center;
            font-size: .6875rem; color: inherit; opacity: .6; font-weight: 700;
        }

        @media (max-width: 640px) {
            .duyuru-tamam { width: 100%; min-width: 0; }
            .duyuru-butonlar { align-items: stretch; }
            .duyuru-ortuk-gorsel { height: 12rem; }
            .duyuru-ortuk-metin { padding: 1.25rem; }
            .duyuru-baslik-acik { font-size: 1.25rem; }
        }
    </style>
@endif
