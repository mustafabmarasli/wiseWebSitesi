@extends('layouts.app')

@section('title', 'Siparişiniz Başarıyla Alındı - Buy WISEly')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 font-sans">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 sm:p-12 text-center">
        <!-- Success Icon -->
        <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-100 shadow-inner">
            <svg class="h-10 w-10 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2">Ödeme Başarılı!</h1>
        <p class="text-slate-500 text-sm mb-8">Siparişiniz başarıyla alındı ve hazırlık sürecine başlandı.</p>

        <!-- Order Details Box -->
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
                    <span class="block text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-0.5">Ödenen Tutar</span>
                    <span class="text-[#1B3A6B] text-sm font-black">{{ number_format($order->total_amount, 2, ',', '.') }} ₺</span>
                </div>
                <div>
                    <span class="block text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-0.5">Ödeme Yöntemi</span>
                    <span class="text-slate-800 text-sm font-bold">{{ $order->payment_method ?? 'iyzico Kredi Kartı' }}</span>
                </div>
                <div>
                    <span class="block text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-0.5">Tahmini Teslimat</span>
                    <span class="text-slate-800 text-sm font-bold">
                        {{ $order->estimated_delivery_at ? $order->estimated_delivery_at->format('d.m.Y') : '3 İş Günü' }}
                    </span>
                </div>
                <div class="sm:col-span-2">
                    <span class="block text-slate-400 font-bold uppercase tracking-wider text-[10px] mb-0.5">Teslimat Adresi</span>
                    <span class="text-slate-800 text-sm font-bold leading-relaxed">
                        {{ $order->address }}, @if(!empty($order->zip_code)){{ $order->zip_code }} @endif{{ $order->city }} / Türkiye
                    </span>
                </div>
            </div>

            <!-- Items -->
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
            </div>
        </div>

        @if(auth()->guest() && $order->user_id === null)
        <!-- Registration Banner/Card -->
        <div class="bg-gradient-to-r from-[#1B3A6B]/5 to-[#1B3A6B]/10 rounded-2xl p-6 border border-[#1B3A6B]/15 text-left mb-8">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-[#1B3A6B]/10 text-[#1B3A6B] flex items-center justify-center shrink-0 border border-[#1B3A6B]/10">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-black text-slate-900 text-sm mb-1">Siparişinizle Birlikte Hızlıca Üye Olun!</h3>
                    <p class="text-xs text-slate-500 font-semibold mb-4">Sipariş bilgilerinizle (<span class="text-slate-800 font-bold">{{ $order->email }}</span>) hemen üyelik oluşturun. Sadece şifrenizi belirlemeniz yeterli!</p>
                    
                    <form action="{{ route('payment.register-guest', $order->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Şifre Belirleyin</label>
                                <input type="password" name="password" id="password" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#1B3A6B]/20 focus:border-[#1B3A6B] transition-all" placeholder="En az 8 karakter">
                                @error('password')
                                    <span class="text-red-500 text-[10px] mt-1 block font-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Şifre Tekrarı</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-[#1B3A6B]/20 focus:border-[#1B3A6B] transition-all" placeholder="Şifrenizi doğrulayın">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="w-full sm:w-auto bg-[#1B3A6B] hover:bg-[#142d54] text-white font-extrabold px-6 py-2.5 rounded-xl text-xs transition-all duration-200 shadow-sm flex items-center justify-center gap-1.5">
                                Üye Ol & Giriş Yap
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('landing') }}" class="bg-[#1B3A6B] hover:bg-[#142d54] text-white font-extrabold px-8 py-3.5 rounded-xl text-sm transition-all duration-200 shadow-md flex items-center justify-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Ana Sayfaya Dön
            </a>
            <button onclick="window.print()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold px-8 py-3.5 rounded-xl text-sm transition-all duration-200 flex items-center justify-center gap-2 border border-slate-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Yazdır / PDF Kaydet
            </button>
        </div>
    </div>
</div>
@endsection