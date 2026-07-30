@extends('layouts.app')

@section('title', 'İleti Tercihleriniz - Buy WISEly')
{{-- Kişiye özel adres: arama sonuçlarına düşmemeli. --}}
@section('robots', 'noindex, nofollow')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">

        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">İleti Tercihleriniz</h1>

        <p class="text-sm text-slate-500 font-semibold mt-2">
            <span class="text-slate-700 font-extrabold">{{ $consent->contact }}</span> için kayıtlı
            ticari elektronik ileti tercihleri.
        </p>

        <div class="mt-6 space-y-3">
            @foreach ($onaylar as $kayit)
                <div class="flex items-center justify-between gap-4 border border-slate-100 rounded-xl px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-sm font-extrabold text-slate-800">{{ $kayit->channel_label }}</p>
                        <p class="text-[11px] font-semibold mt-0.5 {{ $kayit->isGranted() ? 'text-emerald-600' : 'text-slate-400' }}">
                            @if ($kayit->isGranted())
                                Abonesiniz · {{ $kayit->consented_at?->translatedFormat('d F Y') }} tarihinde onay verdiniz
                            @else
                                Çıkış yapıldı · {{ $kayit->revoked_at?->translatedFormat('d F Y') }}
                            @endif
                        </p>
                    </div>

                    @if ($kayit->isGranted())
                        <form method="POST" action="{{ route('marketing.unsubscribe.submit', $consent->unsubscribe_token) }}" class="shrink-0">
                            @csrf
                            <input type="hidden" name="kanal" value="{{ $kayit->channel }}">
                            <button type="submit" class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:border-rose-300 hover:text-rose-600 hover:bg-rose-50 text-xs font-extrabold transition-all active:scale-95">
                                Çık
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('marketing.resubscribe', $consent->unsubscribe_token) }}" class="shrink-0">
                            @csrf
                            <input type="hidden" name="kanal" value="{{ $kayit->channel }}">
                            <button type="submit" class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:border-trendyol hover:text-trendyol text-xs font-extrabold transition-all active:scale-95">
                                Yeniden abone ol
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($onaylar->contains(fn ($k) => $k->isGranted()))
            <form method="POST" action="{{ route('marketing.unsubscribe.submit', $consent->unsubscribe_token) }}" class="mt-6 pt-5 border-t border-slate-100">
                @csrf
                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white py-3 rounded-xl text-sm font-extrabold transition-all active:scale-95">
                    Tümünden çık
                </button>
            </form>
        @endif

        <div class="mt-6 pt-5 border-t border-slate-100 text-xs text-slate-500 font-semibold leading-relaxed space-y-2">
            <p>
                Çıkış yapmanız <strong>sipariş ve kargo bildirimlerini etkilemez</strong> —
                bunlar hizmetin yürütülmesi için gönderilir ve ticari ileti sayılmaz.
            </p>
            <p>
                Talebiniz anında işlenir. Sistemsel gecikme nedeniyle çıkış anında
                gönderilmekte olan bir ileti size ulaşabilir.
            </p>
            <p>
                Sorularınız için <a href="{{ route('contact') }}" class="text-trendyol hover:underline font-extrabold">iletişim sayfamızı</a>
                kullanabilirsiniz.
            </p>
        </div>
    </div>
</div>
@endsection
