{{--
    Duyurunun alt düğmeleri.

    Panelden buton yazısı ve adresi girilmişse asıl eylem o olur ve "Anladım"
    ikincil bir yazıya döner. Buton yoksa eski davranış aynen korunur.
--}}
@php $butonVar = filled($duyuru->button_text) && filled($duyuru->button_url); @endphp

<div class="duyuru-butonlar">
    @if ($butonVar)
        <a href="{{ $duyuru->button_url }}" class="duyuru-tamam">{{ $duyuru->button_text }}</a>
        <button type="button" @click="kapat()" class="duyuru-kapat-yazi">Şimdi değil</button>
    @else
        <button type="button" @click="kapat()" class="duyuru-tamam">Anladım</button>
    @endif
</div>
