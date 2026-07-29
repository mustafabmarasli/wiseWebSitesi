@extends('layouts.app')

@section('title', 'Siparişinizi Aldık - Buy WISEly')
@section('meta_description', 'Siparişiniz alındı. Havale/EFT ödeme bilgileriniz bu sayfada.')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 font-sans">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 sm:p-12">

        <div class="text-center">
            <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-100 shadow-inner">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2">Siparişinizi Aldık!</h1>
            <p class="text-slate-500 text-sm mb-2">
                Sipariş numaranız <span class="font-black text-slate-800">{{ $order->display_number }}</span>
            </p>
            <p class="text-slate-500 text-sm mb-8">
                Ödemeniz hesabımıza geçtiğinde siparişiniz hazırlanmaya başlanacaktır.
            </p>
        </div>

        {{-- ÖDEME BİLGİLERİ — sayfanın asıl amacı, en görünür yerde --}}
        <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-6 sm:p-7 mb-8">
            <div class="flex items-center gap-2.5 mb-5">
                <svg class="h-6 w-6 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <h2 class="text-base font-black text-slate-900">Havale / EFT Ödeme Bilgileri</h2>
            </div>

            <dl class="space-y-4">
                <div>
                    <dt class="text-[10px] font-bold text-amber-700 uppercase tracking-wider mb-1">Hesap Adı (Ünvan)</dt>
                    <dd class="text-sm font-black text-slate-900 leading-snug">{{ $setting->bank_account_holder }}</dd>
                </div>

                @if ($setting->bank_name)
                <div>
                    <dt class="text-[10px] font-bold text-amber-700 uppercase tracking-wider mb-1">Banka</dt>
                    <dd class="text-sm font-bold text-slate-800">{{ $setting->bank_name }}</dd>
                </div>
                @endif

                <div>
                    <dt class="text-[10px] font-bold text-amber-700 uppercase tracking-wider mb-1">IBAN</dt>
                    <dd class="flex flex-wrap items-center gap-2">
                        <span id="iban-text" class="text-sm sm:text-base font-black text-slate-900 tracking-wider break-all">{{ $setting->bank_iban }}</span>
                        <button type="button" onclick="kopyala('iban-text', this)"
                                class="shrink-0 bg-white hover:bg-amber-100 border border-amber-300 text-amber-800 font-extrabold px-3 py-1.5 rounded-lg text-[11px] transition-colors">
                            Kopyala
                        </button>
                    </dd>
                </div>

                <div>
                    <dt class="text-[10px] font-bold text-amber-700 uppercase tracking-wider mb-1">Açıklama (Zorunlu)</dt>
                    <dd class="flex flex-wrap items-center gap-2">
                        <span id="aciklama-text" class="text-sm font-black text-slate-900">Sipariş No: {{ $order->display_number }}</span>
                        <button type="button" onclick="kopyala('aciklama-text', this)"
                                class="shrink-0 bg-white hover:bg-amber-100 border border-amber-300 text-amber-800 font-extrabold px-3 py-1.5 rounded-lg text-[11px] transition-colors">
                            Kopyala
                        </button>
                    </dd>
                </div>

                <div class="pt-4 border-t border-amber-200">
                    <dt class="text-[10px] font-bold text-amber-700 uppercase tracking-wider mb-1">Ödenecek Tutar</dt>
                    <dd class="text-2xl font-black text-trendyol">{{ number_format($order->total_amount, 2, ',', '.') }} ₺</dd>
                </div>
            </dl>

            <p class="mt-5 text-xs text-amber-900 font-semibold leading-relaxed bg-amber-100/70 rounded-xl px-4 py-3">
                Havale/EFT açıklamasına mutlaka <span class="font-black">Sipariş No: {{ $order->display_number }}</span> yazınız.
                Açıklaması olmayan ödemelerin siparişinizle eşleştirilmesi gecikebilir.
            </p>

            @if ($setting->bank_transfer_note)
                <p class="mt-3 text-xs text-slate-600 font-medium leading-relaxed">{{ $setting->bank_transfer_note }}</p>
            @endif
        </div>

        {{-- SİPARİŞ ÖZETİ --}}
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 text-left mb-8">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-200 pb-2 flex justify-between">
                <span>Sipariş Bilgileri</span>
                <span class="text-[#1B3A6B]">{{ $order->display_number }}</span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-semibold text-slate-600 mb-4">
                <div>
                    <span class="block text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-0.5">Alıcı Adı Soyadı</span>
                    <span class="text-slate-800 text-sm font-bold">{{ $order->full_name }}</span>
                </div>
                <div>
                    <span class="block text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-0.5">Telefon Numarası</span>
                    <span class="text-slate-800 text-sm font-bold">{{ $order->masked_phone }}</span>
                </div>
                <div>
                    <span class="block text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-0.5">E-posta Adresi</span>
                    <span class="text-slate-800 text-sm font-bold">{{ $order->masked_email }}</span>
                </div>
                <div>
                    <span class="block text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-0.5">Ödeme Yöntemi</span>
                    <span class="text-slate-800 text-sm font-bold">{{ $order->payment_method }}</span>
                </div>
                <div class="sm:col-span-2">
                    <span class="block text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-0.5">Teslimat Adresi</span>
                    <span class="text-slate-800 text-sm font-bold leading-relaxed">
                        {{ $order->address }}, @if(!empty($order->zip_code)){{ $order->zip_code }} @endif{{ $order->city }} / Türkiye
                    </span>
                </div>
            </div>

            <div class="border-t border-slate-200 pt-4">
                <span class="block text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-3">Sipariş Edilen Ürünler</span>
                <div class="space-y-3">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-slate-800 flex-1 pr-4">{{ $item->product_name }} <span class="text-slate-400 font-medium">x {{ $item->quantity }}</span></span>
                            <span class="font-black text-slate-900">{{ number_format($item->total_price, 2, ',', '.') }} ₺</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-slate-200 mt-4 pt-3 space-y-1.5">
                    @if ($order->discount_amount > 0)
                        <div class="flex justify-between text-xs font-bold text-rose-600">
                            <span>Kupon İndirimi</span>
                            <span>-{{ number_format($order->discount_amount, 2, ',', '.') }} ₺</span>
                        </div>
                    @endif
                    @if ($order->bank_transfer_discount > 0)
                        <div class="flex justify-between text-xs font-bold text-emerald-600">
                            <span>Havale / EFT İndirimi</span>
                            <span>-{{ number_format($order->bank_transfer_discount, 2, ',', '.') }} ₺</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-xs font-bold text-slate-600">
                        <span>Kargo</span>
                        <span>{{ $order->shipping_cost > 0 ? number_format($order->shipping_cost, 2, ',', '.') . ' ₺' : 'Ücretsiz' }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-black text-slate-900 pt-2 border-t border-slate-200">
                        <span>Ödenecek Tutar</span>
                        <span class="text-trendyol">{{ number_format($order->total_amount, 2, ',', '.') }} ₺</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('landing') }}" class="bg-[#1B3A6B] hover:bg-[#142d54] text-white font-extrabold px-8 py-3.5 rounded-xl text-sm transition-all duration-200 shadow-md flex items-center justify-center gap-2">
                Ana Sayfaya Dön
            </a>
            <button onclick="window.print()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold px-8 py-3.5 rounded-xl text-sm transition-all duration-200 flex items-center justify-center gap-2 border border-slate-200">
                Yazdır / PDF Kaydet
            </button>
        </div>
    </div>
</div>

<script>
    function kopyala(id, btn) {
        const metin = document.getElementById(id).innerText.trim();
        const eski  = btn.innerText;

        const bitir = () => {
            btn.innerText = 'Kopyalandı';
            setTimeout(() => { btn.innerText = eski; }, 1500);
        };

        // navigator.clipboard yalnizca guvenli baglamda (https/localhost) var;
        // yoksa eski yontemle kopyalanir.
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(metin).then(bitir).catch(() => {});
            return;
        }

        const alan = document.createElement('textarea');
        alan.value = metin;
        alan.style.position = 'fixed';
        alan.style.opacity = '0';
        document.body.appendChild(alan);
        alan.select();
        try { document.execCommand('copy'); bitir(); } catch (e) {}
        document.body.removeChild(alan);
    }
</script>
@endsection
