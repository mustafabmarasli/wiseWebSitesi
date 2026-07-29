@extends('layouts.app')

@section('title', 'Siparişlerim - Buy WISEly')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 font-sans">
    
    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- Sol Menü (Hesap Navigasyonu) -->
        <div class="w-full md:w-1/4 shrink-0">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-6">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-full bg-[#1B3A6B] text-white flex items-center justify-center font-black">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-black text-slate-800 truncate">{{ auth()->user()->name }}</h3>
                        <p class="text-[10px] font-bold text-slate-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <nav class="space-y-1">
                    <a href="{{ route('profile.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Hesap Bilgilerim</span>
                    </a>
                    
                    <a href="{{ route('profile.favorites') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span>Favorilerim</span>
                    </a>

                    <a href="{{ route('profile.orders') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-black bg-slate-50 text-[#1B3A6B] border border-slate-100 transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Siparişlerim</span>
                    </a>
                    
                    <form action="{{ route('logout') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50/50 transition-all text-left">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Güvenli Çıkış</span>
                        </button>
                    </form>
                </nav>
            </div>
        </div>

        <!-- Sağ Taraf: Sipariş Listesi -->
        <div class="flex-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
                <h1 class="text-xl font-black text-slate-900 mb-6">Sipariş Geçmişim</h1>

                @if($orders->isEmpty())
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 mb-1">Henüz Siparişiniz Yok</h3>
                        <p class="text-xs text-slate-400 font-semibold mb-6">Buy WISEly güvencesiyle ilk siparişinizi hemen oluşturabilirsiniz.</p>
                        <a href="{{ route('landing') }}" class="bg-[#1B3A6B] hover:bg-[#142d54] text-white font-extrabold px-6 py-2.5 rounded-lg text-xs transition-all duration-200 shadow-sm">Alışverişe Başla</a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($orders as $order)
                            <div class="border border-slate-100 rounded-2xl p-4 sm:p-5 hover:border-slate-200 transition-all">
                                <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100 text-xs font-semibold text-slate-500">
                                    <div class="flex items-center gap-4">
                                        <div>
                                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Sipariş No</span>
                                            <span class="text-slate-800 font-black">{{ $order->display_number }}</span>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Tarih</span>
                                            <span class="text-slate-800">{{ $order->created_at->format('d.m.Y H:i') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider text-right">Tutar</span>
                                            <span class="text-[#1B3A6B] font-black text-sm">{{ number_format($order->total_amount, 2, ',', '.') }} ₺</span>
                                        </div>
                                        
                                        <!-- Status Badge -->
                                        @if($order->status === 'paid')
                                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold">Ödendi</span>
                                        @elseif($order->status === 'failed')
                                            <span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold">Başarısız</span>
                                        @else
                                            <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold">Beklemede</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Order Items Preview -->
                                <div class="flex flex-wrap items-center justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-col gap-1">
                                            @foreach($order->items as $item)
                                                <p class="text-xs text-slate-700 font-bold line-clamp-1">
                                                    • {{ $item->product_name }} <span class="text-slate-400 font-medium">(x{{ $item->quantity }})</span>
                                                </p>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="shrink-0">
                                        <a href="{{ route('profile.order-detail', $order->id) }}" class="inline-block bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold px-4 py-2 rounded-lg text-[11px] transition-colors border border-slate-200">Detayları Gör</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
