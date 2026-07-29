{{--
    Paylaşım kısayolları.

    @param string $shareUrl    Paylaşılacak adres (UTM eklenmemiş hâli).
    @param string $shareTitle  Paylaşım metninde geçecek başlık.

    WhatsApp önce geliyor: Türkiye'de paylaşımın büyük çoğunluğu oradan.
    Bağlantıya `utm_source` ekleniyor — Analytics'te hangi kanaldan trafik
    geldiğini ancak böyle görebiliyoruz. Sayfanın `canonical` etiketi
    `url()->current()` kullandığı için sorgu dizisi SEO'yu bölmüyor.
--}}
@php
    $paylasBaslik = $shareTitle ?? '';
    $utm = fn (string $kanal) => $shareUrl . (str_contains($shareUrl, '?') ? '&' : '?')
        . 'utm_source=' . $kanal . '&utm_medium=share';
@endphp

<div class="mt-6 pt-5 border-t border-slate-100" id="share-box"
     data-url="{{ $shareUrl }}"
     data-title="{{ $paylasBaslik }}">

    <div class="flex items-center gap-3 flex-wrap">
        <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wide">Paylaş</span>

        {{-- Cihazın kendi paylaşım menüsü. Varsayılan olarak gizli; JS yalnızca
             dokunmatik cihazda ve tarayıcı destekliyorsa açar. --}}
        <button type="button" id="share-native" onclick="shareNative()"
            class="hidden items-center gap-1.5 px-3.5 py-2 rounded-xl bg-trendyol hover:bg-trendyolDark text-white text-xs font-extrabold transition-all active:scale-95">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342a3 3 0 100-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684zm0-12a3 3 0 105.368-2.684 3 3 0 00-5.368 2.684z" />
            </svg>
            <span>Paylaş</span>
        </button>

        <div class="flex items-center gap-2 flex-wrap" id="share-buttons">
            <a href="https://wa.me/?text={{ urlencode($paylasBaslik . ' - ' . $utm('whatsapp')) }}"
               target="_blank" rel="noopener noreferrer"
               class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 hover:bg-emerald-100 text-xs font-extrabold transition-all active:scale-95"
               aria-label="WhatsApp'ta paylaş">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884a9.82 9.82 0 016.988 2.898 9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.548 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                <span class="hidden sm:inline">WhatsApp</span>
            </a>

            <a href="https://x.com/intent/post?url={{ urlencode($utm('x')) }}&text={{ urlencode($paylasBaslik) }}"
               target="_blank" rel="noopener noreferrer"
               class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-extrabold transition-all active:scale-95"
               aria-label="X'te paylaş">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
                <span class="hidden sm:inline">X</span>
            </a>

            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($utm('facebook')) }}"
               target="_blank" rel="noopener noreferrer"
               class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-sky-50 border border-sky-100 text-sky-700 hover:bg-sky-100 text-xs font-extrabold transition-all active:scale-95"
               aria-label="Facebook'ta paylaş">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span class="hidden sm:inline">Facebook</span>
            </a>

            <button type="button" onclick="copyShareLink(this)"
                class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-extrabold transition-all active:scale-95">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <span class="share-copy-label">Bağlantıyı Kopyala</span>
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        var kutu = document.getElementById('share-box');
        if (!kutu || !navigator.share) return;

        // Masaüstünde açık düğmeler duruyor: orada tek bir "Paylaş" düğmesi
        // kullanıcıyı hangi kanala gideceğini görmekten mahrum bırakıyor.
        if (!window.matchMedia('(pointer: coarse)').matches) return;

        document.getElementById('share-buttons').classList.add('hidden');
        var nativeBtn = document.getElementById('share-native');
        nativeBtn.classList.remove('hidden');
        nativeBtn.classList.add('flex');
    })();

    function shareNative() {
        var kutu = document.getElementById('share-box');

        navigator.share({
            title: kutu.dataset.title,
            url:   kutu.dataset.url + (kutu.dataset.url.indexOf('?') === -1 ? '?' : '&') + 'utm_source=native&utm_medium=share'
        }).catch(function () {
            // Kullanıcı paylaşım menüsünü kapattı — hata değil, sessiz geç.
        });
    }

    function copyShareLink(btn) {
        var kutu  = document.getElementById('share-box');
        var link  = kutu.dataset.url + (kutu.dataset.url.indexOf('?') === -1 ? '?' : '&') + 'utm_source=kopyala&utm_medium=share';
        var label = btn.querySelector('.share-copy-label');

        var bitir = function (basarili) {
            label.textContent = basarili ? 'Kopyalandı!' : 'Kopyalanamadı';
            setTimeout(function () { label.textContent = 'Bağlantıyı Kopyala'; }, 2000);
        };

        // clipboard API yalnızca güvenli bağlamda (https/localhost) çalışır.
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(link).then(function () { bitir(true); }, function () { bitir(false); });
            return;
        }

        var alan = document.createElement('textarea');
        alan.value = link;
        alan.style.position = 'fixed';
        alan.style.opacity = '0';
        document.body.appendChild(alan);
        alan.select();
        try { bitir(document.execCommand('copy')); } catch (e) { bitir(false); }
        document.body.removeChild(alan);
    }
</script>
