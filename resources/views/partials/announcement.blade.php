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
                {{-- Simge: amber uyarı + dalga efekti --}}
                <div class="duyuru-simge">
                    <span class="duyuru-dalga" aria-hidden="true"></span>
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                         stroke-width="1.8" style="position:relative; z-index:1;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
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

        /* Amber uyari simgesi + dalga efekti */
        .duyuru-simge {
            position: relative; width: 4rem; height: 4rem; margin: 0 auto 1.25rem;
            border-radius: 1rem; background: #FFFBEB; color: #F59E0B;
            display: flex; align-items: center; justify-content: center;
            box-shadow: inset 0 2px 6px rgba(245,158,11,.12);
        }
        .duyuru-dalga {
            position: absolute; inset: 0; border-radius: 1rem;
            background: #FBBF24; opacity: .2;
            animation: duyuru-ping 1.8s cubic-bezier(0, 0, .2, 1) infinite;
        }
        @keyframes duyuru-ping {
            75%, 100% { transform: scale(1.35); opacity: 0; }
        }
        /* Hareketi azalt tercihi olan ziyaretcilerde animasyon calismaz */
        @media (prefers-reduced-motion: reduce) {
            .duyuru-dalga { animation: none; }
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
