@extends('layouts.app')

@section('title', 'Ödeme Başarısız - Buy WISEly')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 font-sans">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 sm:p-12 text-center">
        <!-- Error Icon -->
        <div class="w-20 h-20 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6 border border-rose-100 shadow-inner">
            <svg class="h-10 w-10 animate-shake" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>

        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2">Ödeme Başarısız Oldu</h1>
        <p class="text-slate-500 text-sm mb-6">İşlem bankanız veya ödeme sistemi tarafından onaylanmadı.</p>

        <!-- Information Box -->
        <div class="bg-rose-50/50 border border-rose-100 rounded-2xl p-6 text-left mb-8 text-xs sm:text-sm font-semibold text-rose-800 space-y-2">
            <p class="font-extrabold text-rose-900 text-sm">Olası Hata Nedenleri:</p>
            <ul class="list-disc pl-5 space-y-1">
                <li>Kart limitiniz yetersiz olabilir.</li>
                <li>Kart bilgilerini (kart numarası, son kullanma tarihi, CVC) hatalı girmiş olabilirsiniz.</li>
                <li>Kartınız 3D Secure güvenli ödemeye veya internet alışverişlerine kapalı olabilir.</li>
                <li>Geçici bir banka bağlantı hatası oluşmuş olabilir.</li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('checkout') }}" class="bg-[#1B3A6B] hover:bg-[#142d54] text-white font-extrabold px-8 py-3.5 rounded-xl text-sm transition-all duration-200 shadow-md flex items-center justify-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5" />
                </svg>
                Tekrar Dene
            </a>
            <a href="{{ route('cart.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold px-8 py-3.5 rounded-xl text-sm transition-all duration-200 flex items-center justify-center gap-2 border border-slate-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Sepete Geri Dön
            </a>
        </div>
    </div>
</div>

<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}
.animate-shake {
    animation: shake 0.6s ease-in-out;
}
</style>
@endsection