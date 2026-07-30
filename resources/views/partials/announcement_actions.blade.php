{{--
    Duyurunun alt düğmeleri.

    @param \App\Models\Announcement $duyuru
    @param bool $sonKuyrukta  Kuyruğun son duyurusu mu? Değilse kapatma
        düğmesi "Sıradaki duyuru" der — tıklayınca yeni bir pencere açılması
        sürpriz olmasın.

    Panelden buton yazısı ve adresi girilmişse asıl eylem o olur ve kapatma
    ikincil bir yazıya döner.
--}}
@php
    $butonVar    = filled($duyuru->button_text) && filled($duyuru->button_url);
    $son         = $sonKuyrukta ?? true;
    $kapatYazisi = $son ? 'Anladım' : 'Sıradaki duyuru →';
@endphp

<div class="duyuru-butonlar">
    @if ($butonVar)
        <a href="{{ $duyuru->button_url }}" class="duyuru-tamam">{{ $duyuru->button_text }}</a>
        <button type="button" @click="kapat()" class="duyuru-kapat-yazi">{{ $son ? 'Şimdi değil' : 'Sıradaki duyuru →' }}</button>
    @else
        <button type="button" @click="kapat()" class="duyuru-tamam">{{ $kapatYazisi }}</button>
    @endif
</div>
