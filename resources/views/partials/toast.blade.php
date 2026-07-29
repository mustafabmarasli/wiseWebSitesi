{{--
    Bildirim kutusu — sağ üst köşe.

    Alpine KULLANMAZ: `landing.blade.php` bağımsız bir sayfa ve Alpine
    yüklemiyor. Giriş/çıkış/kayıt hep oraya yönlendiği için Alpine'a bağlı
    bir bileşen tam da en çok gerektiği yerde çalışmıyordu.

    İki yoldan tetiklenir:
      1. `session('success' | 'error' | 'info')` — sunucudan gelen mesaj
      2. `window.dispatchEvent(new CustomEvent('show-toast', {
             detail: { message: '...', type: 'success' } }))`
--}}
<div id="app-toast"
     class="fixed top-5 right-5 left-5 sm:left-auto z-[9999] sm:max-w-sm w-auto sm:w-full bg-white rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-slate-100 p-4 flex items-start gap-3 transition-all duration-300 opacity-0 -translate-y-2 pointer-events-none"
     style="display: none;">
    <div id="app-toast-icon" class="rounded-full p-1.5 shrink-0"></div>
    <div class="flex-grow min-w-0">
        <h4 id="app-toast-title" class="text-xs font-black text-slate-800"></h4>
        <p id="app-toast-message" class="text-xs font-medium text-slate-500 mt-0.5"></p>
    </div>
    <button type="button" onclick="hideToast()" class="text-slate-400 hover:text-slate-600 focus:outline-none shrink-0" aria-label="Bildirimi kapat">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

<script>
    (function () {
        var kutu    = document.getElementById('app-toast');
        var zamanci = null;

        var stiller = {
            success: {
                baslik: 'Başarılı!',
                sinif:  'bg-emerald-50 text-emerald-500',
                ikon:   '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4"/></svg>'
            },
            error: {
                baslik: 'Hata!',
                sinif:  'bg-rose-50 text-rose-500',
                ikon:   '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
            },
            info: {
                baslik: 'Bilgi',
                sinif:  'bg-sky-50 text-sky-500',
                ikon:   '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            }
        };

        window.showToast = function (mesaj, tip) {
            var stil = stiller[tip] || stiller.success;

            document.getElementById('app-toast-icon').className = 'rounded-full p-1.5 shrink-0 ' + stil.sinif;
            document.getElementById('app-toast-icon').innerHTML = stil.ikon;
            document.getElementById('app-toast-title').textContent   = stil.baslik;
            document.getElementById('app-toast-message').textContent = mesaj;

            kutu.style.display = 'flex';
            // Geçişin çalışması için görünürlük ile sınıf değişimi ayrı kareye düşmeli.
            requestAnimationFrame(function () {
                kutu.classList.remove('opacity-0', '-translate-y-2', 'pointer-events-none');
            });

            if (zamanci) clearTimeout(zamanci);
            zamanci = setTimeout(hideToast, 4000);
        };

        window.hideToast = function () {
            kutu.classList.add('opacity-0', '-translate-y-2', 'pointer-events-none');
            if (zamanci) clearTimeout(zamanci);
            setTimeout(function () { kutu.style.display = 'none'; }, 300);
        };

        window.addEventListener('show-toast', function (e) {
            showToast(e.detail.message, e.detail.type || 'success');
        });

        // Sepet bildirimi de sağ üstte; ikisi üst üste binmesin.
        window.addEventListener('show-cart-toast', hideToast);

        {{-- JSON_UNESCAPED_UNICODE: aksi hâlde Türkçe karakterler `ş`
             gibi kaçışlara dönüşüyor, sayfa kaynağı okunmaz oluyor. --}}
        @if (session('success'))
            showToast(@json(session('success'), JSON_UNESCAPED_UNICODE), 'success');
        @elseif (session('error'))
            showToast(@json(session('error'), JSON_UNESCAPED_UNICODE), 'error');
        @elseif (session('info'))
            showToast(@json(session('info'), JSON_UNESCAPED_UNICODE), 'info');
        @endif
    })();
</script>
