{{--
    Kargo rozeti. Metni ASLA burada sabitleme — `Setting::shippingNotice()`
    tek karar noktasıdır, eşik panelden değişir.

    @param float|null $subtotal  Bağlamdaki tutar (ürün fiyatı vb.), yoksa null.
--}}
@php
    $kargo = \App\Models\Setting::current()->shippingNotice(isset($subtotal) ? (float) $subtotal : null);
@endphp

<div class="flex items-center gap-1.5 font-bold text-xs mt-3 {{ $kargo['free'] ? 'text-emerald-600' : 'text-slate-500' }}">
    @if ($kargo['free'])
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
    @else
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1" /></svg>
    @endif
    <span>{{ $kargo['title'] }} · {{ $kargo['detail'] }}</span>
</div>
