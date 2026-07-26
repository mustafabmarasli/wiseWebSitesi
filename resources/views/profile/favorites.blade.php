@extends('layouts.app')

@section('title', 'Favorilerim - Buy WISEly')

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
                    
                    <a href="{{ route('profile.favorites') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-black bg-slate-50 text-[#1B3A6B] border border-slate-100 transition-all">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span>Favorilerim</span>
                    </a>

                    <a href="{{ route('profile.orders') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">
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

        <!-- Sağ Taraf: Favori Ürünler -->
        <div class="flex-1">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
                <h1 class="text-xl font-black text-slate-900 mb-6">Favori Ürünlerim</h1>

                @if($products->isEmpty())
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-800 mb-1">Favori Ürününüz Bulunmuyor</h3>
                        <p class="text-xs text-slate-400 font-semibold mb-6">Beğendiğiniz ürünleri favorilerinize ekleyerek bu alanda listeleyebilirsiniz.</p>
                        <a href="{{ route('landing') }}" class="bg-[#1B3A6B] hover:bg-[#142d54] text-white font-extrabold px-6 py-2.5 rounded-lg text-xs transition-all duration-200 shadow-sm">Ürünleri Keşfet</a>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            <div class="relative bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col group h-full">
                                
                                <!-- Remove from favorites button -->
                                <form action="{{ route('favorite.toggle') }}" method="POST" class="absolute top-2.5 right-2.5 z-10">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="bg-white/80 hover:bg-rose-50 text-rose-500 rounded-full p-2.5 shadow-sm border border-slate-100 transition-all hover:scale-110">
                                        <svg class="h-4.5 w-4.5 fill-current" viewBox="0 0 24 24">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    </button>
                                </form>

                                <!-- Product Image -->
                                <a href="{{ route('product.detail', $product->slug) }}" class="block bg-slate-50 border-b border-slate-100 p-4 flex items-center justify-center relative overflow-hidden h-40 group-hover:bg-slate-100/50 transition-colors">
                                    @if ($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="max-h-full max-w-full object-contain transform group-hover:scale-105 transition-all duration-350">
                                    @else
                                        <div class="h-20 w-20 text-slate-300">
                                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                            </svg>
                                        </div>
                                    @endif
                                </a>

                                <!-- Details -->
                                <div class="p-4 flex flex-col flex-1">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $product->category->name }}</span>
                                    <a href="{{ route('product.detail', $product->slug) }}" class="text-xs font-bold text-slate-800 group-hover:text-trendyol line-clamp-2 transition-colors mb-3 flex-grow">{{ $product->name }}</a>

                                    <div class="flex items-baseline gap-2 mb-4">
                                        <span class="text-sm sm:text-base font-black text-slate-900">{{ number_format($product->price, 2, ',', '.') }} TL</span>
                                        @if($product->eski_fiyat && $product->eski_fiyat > $product->price)
                                            <span class="text-[11px] text-slate-400 line-through font-semibold">{{ number_format($product->eski_fiyat, 2, ',', '.') }} TL</span>
                                        @endif
                                    </div>

                                    <!-- Add to Cart / Coming Soon -->
                                    @if ($product->stock <= 0)
                                        <div class="w-full bg-indigo-50 border border-indigo-200 text-indigo-700 py-2 rounded-lg text-xs font-extrabold flex items-center justify-center gap-1.5">
                                            <span>Çok Yakında Sizlerle</span>
                                        </div>
                                    @else
                                        <form action="{{ route('cart.add') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="w-full bg-[#1B3A6B] hover:bg-[#142d54] text-white py-2 rounded-lg text-xs font-extrabold transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                <span>Sepete Ekle</span>
                                            </button>
                                        </form>
                                    @endif
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
