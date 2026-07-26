@php
    $duyuru = \App\Models\Setting::current();
@endphp

@if ($duyuru->showsAnnouncement())
    @php $duyuruAnahtar = 'duyuru_' . md5($duyuru->announcement_title . $duyuru->announcement_text); @endphp

    <div
        x-data="{
            acik: false,
            init() {
                // Ziyaretçi kapattığında oturum boyunca tekrar gösterilmez.
                if (sessionStorage.getItem('duyuru_kapatildi') === @js($duyuruAnahtar)) return;
                setTimeout(() => { this.acik = true }, 350);
            },
            kapat() {
                this.acik = false;
                sessionStorage.setItem('duyuru_kapatildi', @js($duyuruAnahtar));
            },
        }"
        x-show="acik"
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

        {{-- Kart --}}
        <div style="position:relative; width:100%; max-width:32rem; background:#fff; border-radius:1.5rem; box-shadow:0 25px 60px rgba(0,0,0,.35); overflow:hidden;">

            {{-- Üst şerit --}}
            <div style="height:6px; background:linear-gradient(90deg,#1B4A7A,#2DD4BF,#F59E0B);"></div>

            {{-- Kapat --}}
            <button type="button" @click="kapat()" aria-label="Duyuruyu kapat"
                    class="duyuru-kapat" title="Kapat">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div style="padding:2.5rem 2rem 2rem; text-align:center;">
                {{-- Simge --}}
                <div style="width:4rem; height:4rem; margin:0 auto 1.25rem; border-radius:1rem;
                            background:linear-gradient(135deg,#1B4A7A,#14385C);
                            display:flex; align-items:center; justify-content:center;
                            box-shadow:0 10px 25px rgba(27,74,122,.3);">
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46" />
                    </svg>
                </div>

                <h2 id="duyuru-baslik"
                    style="font-size:1.5rem; font-weight:900; color:#0F172A; letter-spacing:-.02em; margin:0;">
                    {{ $duyuru->announcement_title ?: 'Bilgilendirme' }}
                </h2>

                <p style="margin:.75rem auto 0; max-width:26rem; font-size:.9375rem; line-height:1.6; color:#475569; font-weight:500;">
                    {{ $duyuru->announcement_text }}
                </p>

                <button type="button" @click="kapat()" class="duyuru-tamam">Anladım</button>

                <p style="margin-top:1rem; font-size:.6875rem; color:#94A3B8; font-weight:600;">
                    Ürünleri şimdiden inceleyebilirsiniz
                </p>
            </div>
        </div>
    </div>

    <style>
        /* Yerleşim CSS'te: x-show yalnizca display'i yonetsin.
           Alpine gizlerken inline display:none yazar, gosterirken kaldirir;
           o an bu siniftaki display:flex devreye girer. */
        .duyuru-katman {
            position: fixed; inset: 0; z-index: 10000;
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
        }

        .duyuru-kapat {
            position: absolute; top: 1rem; right: 1rem;
            width: 2.25rem; height: 2.25rem; border: 0; border-radius: 9999px;
            display: flex; align-items: center; justify-content: center;
            background: transparent; color: #94A3B8; cursor: pointer;
            transition: background-color .15s, color .15s, transform .1s;
        }
        .duyuru-kapat:hover { background: #F1F5F9; color: #334155; }
        .duyuru-kapat:active { transform: scale(.94); }

        .duyuru-tamam {
            margin-top: 1.75rem; min-width: 200px;
            background: #1B4A7A; color: #fff; border: 0;
            font-weight: 800; font-size: .875rem;
            padding: .875rem 2rem; border-radius: .75rem; cursor: pointer;
            box-shadow: 0 4px 12px rgba(27,74,122,.25);
            transition: background-color .15s, transform .1s, box-shadow .15s;
        }
        .duyuru-tamam:hover { background: #14385C; box-shadow: 0 6px 18px rgba(27,74,122,.35); }
        .duyuru-tamam:active { transform: scale(.98); }

        @media (max-width: 640px) {
            .duyuru-tamam { width: 100%; min-width: 0; }
        }
    </style>
@endif
