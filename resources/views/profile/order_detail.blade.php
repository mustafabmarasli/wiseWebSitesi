@extends('layouts.app')

@section('title', 'Sipariş Detayı - Buy WISEly')

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

        <!-- Sağ Taraf: Sipariş Detayları -->
        <div class="flex-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
                
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                    <div>
                        <a href="{{ route('profile.orders') }}" class="text-xs font-bold text-slate-400 hover:text-[#1B3A6B] flex items-center gap-1.5 transition-colors mb-1.5">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Siparişlerime Dön
                        </a>
                        <h1 class="text-xl font-black text-slate-900">Sipariş Detayı</h1>
                    </div>
                    <span class="text-xs font-black text-slate-400 bg-slate-50 border border-slate-100 px-3 py-1 rounded-lg">{{ $order->display_number }}</span>
                </div>

                <!-- Sipariş Takip Stepper -->
                @if(in_array($order->status, ['paid', 'shipped', 'delivered']))
                    <div class="mb-8 bg-slate-50 border border-slate-100 p-6 rounded-2xl">
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider mb-6">Sipariş Takip Durumu</h3>
                        <div class="relative flex items-center justify-between">
                            <!-- Progress Line Background -->
                            <div class="absolute left-4 right-4 top-4 h-1 bg-slate-200 -z-0 rounded-full"></div>
                            
                            <!-- Dynamic Progress Line Foreground -->
                            @php
                                $percent = 0;
                                if ($order->status === 'paid') $percent = 15; // Sipariş Alındı (Hazırlanıyor adımına doğru)
                                elseif ($order->status === 'shipped') $percent = 60; // Kargoya Verildi
                                elseif ($order->status === 'delivered') $percent = 100; // Teslim Edildi
                            @endphp
                            <div class="absolute left-4 top-4 h-1 bg-emerald-500 -z-0 rounded-full transition-all duration-500" style="width: calc({{ $percent }}% - 8px);"></div>

                            <!-- Steps -->
                            <!-- Step 1: Sipariş Alındı -->
                            <div class="flex flex-col items-center relative z-10 w-16">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-extrabold bg-emerald-500 text-white ring-4 ring-emerald-100">
                                    ✓
                                </div>
                                <span class="text-[10px] font-black text-slate-800 mt-2 text-center">Sipariş Alındı</span>
                            </div>

                            <!-- Step 2: Hazırlanıyor -->
                            <div class="flex flex-col items-center relative z-10 w-16">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-extrabold {{ in_array($order->status, ['paid', 'shipped', 'delivered']) ? 'bg-emerald-500 text-white ring-4 ring-emerald-100' : 'bg-slate-200 text-slate-500' }}">
                                    2
                                </div>
                                <span class="text-[10px] font-black text-slate-800 mt-2 text-center">Hazırlanıyor</span>
                            </div>

                            <!-- Step 3: Kargoya Verildi -->
                            <div class="flex flex-col items-center relative z-10 w-16">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-extrabold {{ in_array($order->status, ['shipped', 'delivered']) ? 'bg-emerald-500 text-white ring-4 ring-emerald-100' : 'bg-slate-200 text-slate-500' }}">
                                    3
                                </div>
                                <span class="text-[10px] font-black text-slate-800 mt-2 text-center">Kargoya Verildi</span>
                            </div>

                            <!-- Step 4: Teslim Edildi -->
                            <div class="flex flex-col items-center relative z-10 w-16">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-extrabold {{ $order->status === 'delivered' ? 'bg-emerald-500 text-white ring-4 ring-emerald-100' : 'bg-slate-200 text-slate-500' }}">
                                    4
                                </div>
                                <span class="text-[10px] font-black text-slate-800 mt-2 text-center">Teslim Edildi</span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Status Summary -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    <div class="bg-slate-50 border border-slate-100 p-4 rounded-xl">
                        <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Sipariş Durumu</span>
                        @if($order->status === 'paid')
                            <span class="inline-flex items-center gap-1 text-emerald-700 font-black text-sm">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Ödendi / Hazırlanıyor
                            </span>
                        @elseif($order->status === 'shipped')
                            <span class="inline-flex items-center gap-1 text-emerald-700 font-black text-sm">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Kargoya Verildi
                            </span>
                        @elseif($order->status === 'delivered')
                            <span class="inline-flex items-center gap-1 text-emerald-700 font-black text-sm">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Teslim Edildi
                            </span>
                        @elseif($order->status === 'failed')
                            <span class="inline-flex items-center gap-1 text-rose-700 font-black text-sm">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Ödeme Başarısız
                            </span>
                        @elseif($order->status === 'cancelled')
                            <span class="inline-flex items-center gap-1 text-slate-500 font-black text-sm">
                                <span class="w-2 h-2 rounded-full bg-slate-450"></span> İptal Edildi
                            </span>
                        @elseif($order->status === 'refunded')
                            <span class="inline-flex items-center gap-1 text-blue-700 font-black text-sm">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span> İade Edildi
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-amber-700 font-black text-sm">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Ödeme Bekleniyor
                            </span>
                        @endif
                    </div>
                    <div class="bg-slate-50 border border-slate-100 p-4 rounded-xl">
                        <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Tarih</span>
                        <span class="text-slate-800 font-bold text-sm">{{ $order->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    <div class="bg-slate-50 border border-slate-100 p-4 rounded-xl">
                        <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Ödeme Yöntemi</span>
                        <span class="text-[#1B3A6B] font-bold text-sm">{{ $order->payment_method ?? 'iyzico Kredi Kartı' }}</span>
                    </div>
                    <div class="bg-slate-50 border border-slate-100 p-4 rounded-xl">
                        <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1">Tahmini Teslimat</span>
                        <span class="text-slate-800 font-bold text-sm">
                            {{ $order->estimated_delivery_at ? $order->estimated_delivery_at->format('d.m.Y') : '3 İş Günü' }}
                        </span>
                    </div>
                </div>

                <!-- Customer Details & Shipping Address -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider mb-3">Müşteri Bilgileri</h3>
                        <div class="space-y-1.5 text-xs font-bold text-slate-600">
                            <p class="text-slate-800">{{ $order->full_name }}</p>
                            <p>{{ $order->masked_phone }}</p>
                            <p>{{ $order->masked_email }}</p>
                            <p>T.C. Kimlik No: {{ substr($order->identity_number, 0, 3) . '********' }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider mb-3">Teslimat Adresi</h3>
                        <div class="space-y-1.5 text-xs font-bold text-slate-600">
                            <p class="text-slate-800 leading-relaxed">{{ $order->address }}</p>
                            <p>@if(!empty($order->zip_code)){{ $order->zip_code }} @endif{{ $order->city }} / Türkiye</p>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="border border-slate-100 rounded-2xl overflow-hidden mb-8">
                    <div class="bg-slate-50 px-5 py-3 border-b border-slate-100 flex justify-between items-center">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Ürün Bilgisi</span>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Toplam</span>
                    </div>
                    
                    <div class="divide-y divide-slate-100 px-5">
                        @foreach($order->items as $item)
                            <div class="py-4 flex justify-between items-center gap-4 text-xs">
                                <div class="min-w-0">
                                    <p class="text-slate-800 font-bold">{{ $item->product_name }}</p>
                                    <p class="text-slate-400 font-semibold mt-1">{{ number_format($item->unit_price, 2, ',', '.') }} ₺ x {{ $item->quantity }}</p>
                                </div>
                                <span class="font-black text-slate-900 shrink-0">{{ number_format($item->total_price, 2, ',', '.') }} ₺</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="bg-slate-50/50 px-5 py-4 border-t border-slate-100 flex flex-col items-end gap-2 text-xs font-semibold text-slate-500">
                        <div class="flex justify-between w-full sm:max-w-xs">
                            <span>Kargo ({{ $order->shipping_method ?? 'Standart Kargo' }})</span>
                            <span class="{{ $order->shipping_cost > 0 ? 'text-slate-800 font-bold' : 'text-emerald-600 font-extrabold' }}">
                                {{ $order->shipping_cost > 0 ? number_format($order->shipping_cost, 2, ',', '.') . ' ₺' : 'Ücretsiz' }}
                            </span>
                        </div>
                        <div class="flex justify-between w-full sm:max-w-xs text-sm font-black text-slate-900 border-t border-slate-150 pt-2 mt-1">
                            <span>Genel Toplam</span>
                            <span class="text-[#1B3A6B]">{{ number_format($order->total_amount, 2, ',', '.') }} ₺</span>
                        </div>
                    </div>
                </div>

                <!-- Print Options -->
                <div class="flex justify-end gap-4">
                    <button onclick="window.print()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold px-6 py-2.5 rounded-lg text-xs transition-all duration-200 flex items-center gap-1.5 border border-slate-200">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Siparişi Yazdır
                    </button>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
