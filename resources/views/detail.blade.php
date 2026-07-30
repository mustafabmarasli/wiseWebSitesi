@extends('layouts.app')

@section('title', ($product->meta_title ?? $product->name) . ' - Buy WISEly')
@section('meta_description', $product->meta_description ?? Str::limit(strip_tags($product->description), 150))

{{-- Sosyal paylaşım: WhatsApp/Instagram'da link paylaşıldığında görsel + başlık çıksın --}}
@section('og_type', 'product')
@section('og_title', $product->name)
@section('og_description', Str::limit(strip_tags($product->description), 200))
@if ($product->image_url)
    @section('og_image', $product->image_url)
@endif

{{-- Yapısal veri: Google arama sonucunda fiyat ve stok durumu gösterir.
     aggregateRating BİLİNÇLİ OLARAK YOK — puanlar gerçek müşteri yorumu değil
     (seed verisi), uydurma değerlendirme Google politikası ihlalidir. --}}
@section('schema')
@php
    $urunSemasi = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $product->name,
        'description' => Str::limit(strip_tags($product->description), 300),
        'sku'         => (string) $product->id,
        'image'       => array_values(array_filter(
            array_merge([$product->image_url], $product->additional_image_urls)
        )),
        'category'    => $product->category?->name,
        'offers'      => [
            '@type'         => 'Offer',
            'url'           => url()->current(),
            'price'         => number_format((float) $product->price, 2, '.', ''),
            'priceCurrency' => 'TRY',
            'availability'  => $product->stock > 0
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller'        => ['@type' => 'Organization', 'name' => 'Wise Solutions'],
        ],
    ];

    $kirintiSemasi = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => array_values(array_filter([
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => route('landing')],
            $product->category ? [
                '@type'    => 'ListItem',
                'position' => 2,
                'name'     => $product->category->name,
                'item'     => route('category', $product->category->slug),
            ] : null,
            ['@type' => 'ListItem', 'position' => 3, 'name' => $product->name, 'item' => url()->current()],
        ])),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($urunSemasi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode($kirintiSemasi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3 font-sans">
        <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Anasayfa</a>
            <span>/</span>
            <a href="{{ route('category', $product->category->slug) }}" class="hover:text-trendyol">{{ $product->category->name }}</a>
            <span>/</span>
            <span class="text-slate-700">{{ $product->name }}</span>
        </div>
    </div>

    <!-- Product Detail Block -->
    <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                
                <!-- Left: Visual Illustration / Image Container -->
                <div x-data="{ 
                    lightboxOpen: false, 
                    activeImage: @js($product->image_url),
                    init() {}
                }" class="lg:col-span-5 flex flex-col gap-4">
                    <div @click="lightboxOpen = true; openLightbox();" class="cursor-pointer group flex items-center justify-center bg-slate-50 border border-slate-100 rounded-2xl p-8 h-80 sm:h-[400px] relative overflow-hidden hover:bg-slate-100/50 transition-all duration-300 shadow-sm hover:shadow-md" id="main-image-container">
                        <div class="absolute bottom-3 right-3 bg-white/80 backdrop-blur-sm p-2 rounded-xl border border-slate-200/55 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-10" title="Resmi Büyüt">
                            <svg class="w-4 h-4 text-slate-550" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                            </svg>
                        </div>
                        
                        @if ($product->image_url)
                            <!-- Large Actual Product Image -->
                            <img :src="activeImage" alt="{{ $product->name }}" class="object-contain h-full w-full max-h-72 sm:max-h-[350px] group-hover:scale-105 transition-all duration-300" id="main-product-img">
                        @else
                            @if (Str::contains(Str::lower($product->name), 'beetle') || Str::contains(Str::lower($product->name), 'dfr1117'))
                                <!-- Large detailed Beetle ESP32-C6 SVG -->
                                <svg class="h-64 w-64 text-orange-600 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="25" y="30" width="50" height="40" rx="4" fill="#1b1b1b" stroke="#F27A1A" stroke-width="1.5" />
                                    <rect x="35" y="38" width="30" height="24" rx="1" fill="#CCCCCC" />
                                    <rect x="38" y="41" width="24" height="18" fill="#222222" />
                                    <circle cx="44" cy="46" r="1" fill="#F27A1A" />
                                    <rect x="22" y="34" width="3" height="32" fill="#D4AF37" />
                                    <rect x="75" y="34" width="3" height="32" fill="#D4AF37" />
                                    <circle cx="68" cy="62" r="1.5" fill="#E2E8F0" />
                                </svg>
                            @elseif (Str::contains(Str::lower($product->name), 'devkitc') || Str::contains(Str::lower($product->name), 'esp32-c6-devkit'))
                                <!-- Large detailed ESP32-C6 DevKitC -->
                                <svg class="h-64 w-64 text-slate-800 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="25" y="15" width="50" height="70" rx="3" fill="#1E293B" />
                                    <rect x="30" y="24" width="40" height="28" rx="1" fill="#94A3B8" />
                                    <rect x="33" y="27" width="34" height="22" fill="#334155" />
                                    <rect x="32" y="81" width="12" height="5" fill="#CBD5E1" />
                                    <rect x="56" y="81" width="12" height="5" fill="#CBD5E1" />
                                    <circle cx="50" cy="62" r="2.5" fill="#FF0000" />
                                </svg>
                            @elseif (Str::contains(Str::lower($product->name), 's tipi') || Str::contains(Str::lower($product->name), 'bükülebilir'))
                                <!-- Large detailed S-Type LED Strip -->
                                <svg class="h-64 w-64 text-yellow-500 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 30c20 0 10 20 35 20s15 20 35 20" stroke="#E2E8F0" stroke-width="8" stroke-linecap="round" />
                                    <path d="M15 30c20 0 10 20 35 20s15 20 35 20" stroke="#F59E0B" stroke-width="2" stroke-dasharray="1 6" stroke-linecap="round" />
                                    <rect x="20" y="28" width="4" height="4" fill="#FFE57F" stroke="#F59E0B" />
                                    <rect x="38" y="38" width="4" height="4" fill="#FFE57F" stroke="#F59E0B" />
                                    <rect x="62" y="58" width="4" height="4" fill="#FFE57F" stroke="#F59E0B" />
                                    <rect x="80" y="68" width="4" height="4" fill="#FFE57F" stroke="#F59E0B" />
                                </svg>
                            @elseif (Str::contains(Str::lower($product->name), 'cob'))
                                <!-- Large detailed COB LED Strip -->
                                <svg class="h-64 w-64 text-yellow-500 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="10" y="44" width="80" height="12" rx="3" fill="#D4AF37" />
                                    <rect x="12" y="46" width="76" height="8" rx="2" fill="#F59E0B" class="animate-pulse" />
                                    <line x1="12" y1="50" x2="88" y2="50" stroke="#FFFFFF" stroke-width="2" stroke-dasharray="2 3" />
                                </svg>
                            @elseif (Str::contains(Str::lower($product->name), 'esp32-cam'))
                                <!-- Large detailed ESP32-CAM -->
                                <svg class="h-64 w-64 text-slate-800 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="25" y="20" width="50" height="60" rx="3" fill="#1e293b" />
                                    <circle cx="50" cy="40" r="10" fill="#222222" stroke="#475569" stroke-width="2" />
                                    <circle cx="50" cy="40" r="4" fill="#000000" />
                                    <rect x="44" y="34" width="12" height="12" stroke="#FFFFFF" stroke-width="0.8" />
                                    <rect x="35" y="68" width="30" height="12" fill="#CBD5E1" />
                                    <rect x="32" y="55" width="6" height="6" fill="#FFF" />
                                </svg>
                            @elseif (Str::contains(Str::lower($product->name), 'supermini') || Str::contains(Str::lower($product->name), 'super mini'))
                                <!-- Large detailed SuperMini -->
                                <svg class="h-64 w-64 text-slate-800 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="32" y="20" width="36" height="60" rx="4" fill="#0F172A" stroke="#475569" />
                                    <rect x="38" y="32" width="24" height="24" fill="#1E293B" />
                                    <circle cx="50" cy="44" r="3" fill="#334155" />
                                    <rect x="42" y="75" width="16" height="5" fill="#E2E8F0" />
                                </svg>
                            @elseif (Str::contains(Str::lower($product->name), 'd1 mini'))
                                <!-- Large detailed D1 Mini -->
                                <svg class="h-64 w-64 text-slate-800 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="28" y="25" width="44" height="50" rx="4" fill="#1E293B" />
                                    <rect x="34" y="32" width="32" height="24" fill="#334155" />
                                    <rect x="40" y="70" width="20" height="5" fill="#CBD5E1" />
                                </svg>
                            @elseif (Str::contains(Str::lower($product->name), 'devkit'))
                                <!-- Large detailed DevKit -->
                                <svg class="h-64 w-64 text-slate-800 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="25" y="15" width="50" height="70" rx="3" fill="#1E293B" />
                                    <rect x="35" y="25" width="30" height="25" fill="#334155" />
                                    <rect x="40" y="77" width="20" height="8" fill="#CBD5E1" />
                                </svg>
                            @elseif (Str::contains(Str::lower($product->name), 'lens kabı') || Str::contains(Str::lower($product->name), 'saklama kutusu'))
                                <!-- Large detailed Lens Case -->
                                <svg class="h-64 w-64 text-sky-505 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="15" y="35" width="70" height="30" rx="15" fill="#E2E8F0" stroke="#CBD5E1" stroke-width="2" />
                                    <circle cx="32" cy="50" r="11" fill="#3B82F6" />
                                    <circle cx="32" cy="50" r="8" fill="#60A5FA" />
                                    <text x="30" y="53" fill="#FFFFFF" font-size="9" font-family="sans-serif" font-weight="bold">L</text>
                                    <circle cx="68" cy="50" r="11" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="1.5" />
                                    <circle cx="68" cy="50" r="8" fill="#F8FAFC" />
                                    <text x="65" y="53" fill="#64748B" font-size="9" font-family="sans-serif" font-weight="bold">R</text>
                                </svg>
                            @elseif (Str::contains(Str::lower($product->name), 'dmv'))
                                <!-- Large detailed DMV Suction Tool -->
                                <svg class="h-64 w-64 text-rose-500 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M35 30 C35 30, 50 15, 65 30 L62 33 C62 33, 50 24, 38 33 Z" fill="#EF4444" />
                                    <rect x="47" y="32" width="6" height="42" rx="3" fill="#EF4444" />
                                    <ellipse cx="50" cy="74" rx="10" ry="14" fill="#DC2626" />
                                    <ellipse cx="47" cy="72" rx="3" ry="5" fill="#F87171" opacity="0.6" />
                                </svg>
                            @elseif (Str::contains(Str::lower($product->name), 'arduino'))
                                <!-- Large detailed Arduino SVG -->
                                <svg class="h-64 w-64 text-sky-700 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="10" y="20" width="80" height="60" rx="6" fill="#008184" />
                                    <rect x="5" y="30" width="15" height="12" rx="1" fill="#CCCCCC" />
                                    <rect x="7" y="32" width="11" height="8" fill="#AAAAAA" />
                                    <rect x="5" y="55" width="18" height="15" rx="1" fill="#333333" />
                                    <rect x="35" y="42" width="40" height="14" rx="1" fill="#222222" />
                                    <circle cx="38" cy="45" r="1.5" fill="#555555" />
                                    <rect x="25" y="22" width="55" height="4" fill="#333333" />
                                    <rect x="30" y="74" width="45" height="4" fill="#333333" />
                                    <rect x="72" y="32" width="6" height="6" fill="#F27A1A" />
                                    <circle cx="82" cy="62" r="3" fill="#E5E7EB" />
                                    <circle cx="28" cy="48" r="2.5" fill="#E5E7EB" />
                                </svg>
                            @elseif (Str::contains(Str::lower($product->name), 'raspberry'))
                                <!-- Large detailed Raspberry Pi SVG -->
                                <svg class="h-64 w-64 text-emerald-700 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="10" y="20" width="80" height="60" rx="8" fill="#C31C4A" />
                                    <rect x="78" y="28" width="16" height="20" rx="1" fill="#DDDDDD" />
                                    <rect x="78" y="52" width="16" height="11" rx="1" fill="#CCCCCC" />
                                    <rect x="78" y="66" width="16" height="11" rx="1" fill="#CCCCCC" />
                                    <rect x="38" y="38" width="22" height="22" rx="2" fill="#222222" />
                                    <rect x="52" y="74" width="10" height="9" rx="1" fill="#222222" />
                                    <rect x="18" y="22" width="50" height="4" fill="#333333" />
                                    <rect x="18" y="74" width="50" height="4" fill="#333333" />
                                </svg>
                            @else
                                <!-- Large electronic board default SVG -->
                                <svg class="h-64 w-64 text-slate-700 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="15" y="15" width="70" height="70" rx="6" fill="#1E293B" />
                                    <rect x="30" y="30" width="40" height="40" rx="3" fill="#334155" />
                                    <circle cx="50" cy="50" r="4" fill="#F27A1A" />
                                </svg>
                            @endif
                        @endif
                    </div>

                    <!-- Thumbnails Gallery -->
                    @if ($product->image_url && count($product->additional_image_urls) > 0)
                        <div class="flex flex-wrap gap-2.5 pt-1.5" id="product-thumbnails">
                            @foreach (array_merge([$product->image_url], $product->additional_image_urls) as $thumbUrl)
                                <button @click="activeImage = @js($thumbUrl)"
                                    :class="activeImage === @js($thumbUrl) ? 'border-trendyol ring-1 ring-trendyol' : 'border-slate-200 hover:border-slate-350'"
                                    class="w-16 h-16 bg-slate-50 border-2 rounded-xl p-1 flex items-center justify-center shrink-0 transition-all duration-200 focus:outline-none">
                                    <img src="{{ $thumbUrl }}" alt="{{ $product->name }}" loading="lazy" class="object-contain max-h-full max-w-full rounded-lg">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <!-- Lightbox Modal Component -->
                    <div x-show="lightboxOpen" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" style="display: none;" @keydown.escape.window="lightboxOpen = false">
                        <div class="absolute inset-0" @click="lightboxOpen = false"></div>
                        <div class="relative bg-white rounded-3xl p-6 max-w-2xl w-full max-h-[90vh] overflow-auto shadow-2xl flex flex-col items-center justify-center" @click.stop x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                            <button @click="lightboxOpen = false" class="absolute top-4 right-4 bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-700 p-2.5 rounded-full transition-colors focus:outline-none z-20">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <div class="p-4 flex items-center justify-center max-h-[70vh] w-full" id="lightbox-content-target">
                                <!-- JS will dynamically clone content here to prevent duplicate markup -->
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 mt-4 text-center">{{ $product->name }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Right: Product Information & Checkout simulation -->
                <div class="lg:col-span-7 flex flex-col justify-between">
                    <div>
                        
                        <!-- Breadcrumb/Category -->
                        <div class="text-xs font-bold text-trendyol uppercase tracking-wider mb-2">
                            {{ $product->category->name }}
                        </div>
                        
                        {{-- Başlık + favori.
                             Kalp bilerek BURADA: eskiden sepete ekle satırının
                             en sonundaydı, mobilde flex-col yüzünden turuncu
                             düğmenin altına düşüyor ve görmek için kaydırmak
                             gerekiyordu. Sağ üst köşe müşterinin bakmaya
                             alışkın olduğu yer. --}}
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <h1 class="text-xl sm:text-3xl font-extrabold text-slate-900 tracking-tight" id="product-detail-title">
                                {{ $product->name }}
                            </h1>

                            @auth
                                @php $isFav = auth()->user()->favoriteProducts->contains($product->id); @endphp
                                <button type="button"
                                    id="fav-btn"
                                    data-product="{{ $product->id }}"
                                    data-fav="{{ $isFav ? '1' : '0' }}"
                                    onclick="toggleFavorite()"
                                    class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl border text-xs font-extrabold transition-all active:scale-95 {{ $isFav ? 'border-rose-200 bg-rose-50 text-rose-600' : 'border-slate-200 text-slate-500 hover:border-rose-200 hover:text-rose-600 hover:bg-rose-50' }}">
                                    <svg id="fav-icon" class="h-5 w-5 shrink-0" fill="{{ $isFav ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    {{-- Yalnız simge ne demek olduğunu anlatmıyor. --}}
                                    <span id="fav-label" class="hidden sm:inline whitespace-nowrap">{{ $isFav ? 'Favorilerimde' : 'Favorilerime Ekle' }}</span>
                                </button>
                            @else
                                <a href="{{ route('login') }}"
                                    class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 text-slate-500 hover:border-rose-200 hover:text-rose-600 hover:bg-rose-50 text-xs font-extrabold transition-all active:scale-95"
                                    title="Favorilerime eklemek için giriş yapın">
                                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    <span class="hidden sm:inline whitespace-nowrap">Favorilerime Ekle</span>
                                </a>
                            @endauth
                        </div>
                        
                        <!-- Ratings -->
                        @if ($product->rating > 0)
                        <div class="flex items-center gap-2 mb-4" id="product-detail-ratings">
                            <div class="flex items-center text-amber-500 gap-0.5">
                                @for ($i = 0; $i < 5; $i++)
                                    @if ($i < floor($product->rating))
                                        <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                    @else
                                        <svg class="h-4 w-4 text-slate-300 stroke-current" viewBox="0 0 20 20" fill="none"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" stroke-width="2" /></svg>
                                    @endif
                                @endfor
                            </div>
                            <span class="text-sm font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded">{{ number_format($product->rating, 1) }}</span>
                            <span class="text-xs text-slate-400 font-semibold">(24 Değerlendirme)</span>
                        </div>
                        @endif

                        <!-- Price and Stock Details -->
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-5 mb-4" id="product-detail-pricebox">
                            <div class="flex items-baseline gap-3 mb-2 flex-wrap">
                                @if ($product->eski_fiyat && $product->eski_fiyat > $product->price)
                                    <span class="text-sm text-slate-400 line-through font-semibold">{{ number_format($product->eski_fiyat, 2, ',', '.') }} TL</span>
                                    <span class="text-2xl sm:text-3xl font-black text-rose-600">{{ number_format($product->price, 2, ',', '.') }} TL</span>
                                    <span class="bg-rose-100 text-rose-700 text-xs font-bold px-2 py-0.5 rounded">Fırsat Ürünü</span>
                                @else
                                    <span class="text-2xl sm:text-3xl font-black text-slate-900">{{ number_format($product->price, 2, ',', '.') }} TL</span>
                                @endif
                            </div>
                            
                            {{-- Kargo rozeti: koşulu ayardan okur. Buraya "Kargo
                                 Bedava" yazmak, eşiğin altındaki sepette ödeme
                                 adımında sürpriz ücret çıkarmak demekti. --}}
                            @include('partials.shipping_notice', ['subtotal' => $product->price])
                        </div>

                        <!-- Social Proof Badge -->
                        <div class="mb-4 flex flex-wrap gap-2.5 items-center text-[10px] sm:text-xs font-bold text-slate-500">
                            <div class="flex items-center gap-1 bg-amber-50 border border-amber-100 rounded-lg px-2.5 py-1 text-amber-700">
                                <span>🔥</span>
                                <span>Bu ürünü son 24 saatte 18 kişi inceledi!</span>
                            </div>
                            <div class="flex items-center gap-1 bg-sky-50 border border-sky-100 rounded-lg px-2.5 py-1 text-sky-750">
                                <span>🚚</span>
                                <span>14:00 öncesi siparişler bugün kargoda!</span>
                            </div>
                        </div>

                        <!-- Meta Info / Seller -->
                        <div class="text-xs space-y-2 text-slate-550 mb-6 font-bold">
                            <div>Satıcı: <span class="text-trendyol font-extrabold">Wise Solutions</span></div>
                            <div class="flex items-center gap-1.5">
                                <span>Stok Durumu:</span>
                                @if ($product->stock > 5)
                                    <span class="text-emerald-600 font-extrabold flex items-center gap-1"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Stokta Var ({{ $product->stock }} adet)</span>
                                @elseif ($product->stock > 0)
                                    <span class="text-rose-600 font-extrabold flex items-center gap-1"><span class="w-1.5 h-1.5 bg-rose-500 rounded-full animate-ping"></span> Son {{ $product->stock }} Ürün!</span>
                                @else
                                    <span class="text-slate-400 font-extrabold flex items-center gap-1"><span class="w-1.5 h-1.5 bg-slate-350 rounded-full"></span> Tükendi</span>
                                @endif
                            </div>
                        </div>

                    </div>

                    <!-- Buy / Add to Cart Form -->
                    <form action="{{ route('cart.add') }}" method="POST" class="border-t border-slate-100 pt-6">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        
                        {{-- Geniş kapta (1680px) bu kolon 880px'e çıkıyor ve
                             "Sepete Ekle" düğmesi o genişliğe uzayıp çirkin
                             duruyordu. Ayırıcı çizgi ve fiyat kutusu geniş
                             kalır, yalnızca kontroller sınırlanır. --}}
                        <div class="flex flex-col sm:flex-row gap-4 items-center xl:max-w-2xl">
                            
                            <!-- Quantity selector -->
                            @if ($product->stock > 0)
                                <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden shrink-0" id="quantity-control-box">
                                    <button type="button" onclick="changeQty(-1)" class="px-3.5 py-2.5 bg-slate-50 hover:bg-slate-100 font-bold text-slate-600 text-sm border-r border-slate-200 active:scale-95 transition-all">-</button>
                                    <input type="number" name="quantity" id="quantity-input" value="1" min="1" max="{{ $product->stock }}" class="w-12 text-center text-sm font-bold text-slate-800 focus:outline-none py-2" readonly>
                                    <button type="button" onclick="changeQty(1)" class="px-3.5 py-2.5 bg-slate-50 hover:bg-slate-100 font-bold text-slate-600 text-sm border-l border-slate-200 active:scale-95 transition-all">+</button>
                                </div>
                            @endif

                            <!-- Cart add button -->
                            @if ($product->stock <= 0)
                                <button 
                                    type="button" 
                                    onclick="notifyStock({{ $product->id }})"
                                    class="w-full bg-slate-150 hover:bg-slate-200 text-slate-700 py-3 rounded-xl text-sm sm:text-base font-extrabold transition-all flex items-center justify-center gap-2 active:scale-95 border border-slate-200/50"
                                    id="btn-notify-stock"
                                >
                                    <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span>Stok Gelince Haber Ver</span>
                                </button>
                            @else
                                <button 
                                    type="submit" 
                                    class="w-full bg-trendyol hover:bg-trendyolDark text-white py-3 rounded-xl text-sm sm:text-base font-extrabold transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow active:scale-95 hover:scale-[1.01]"
                                    id="btn-add-to-cart-page"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>Sepete Ekle</span>
                                </button>
                            @endif

                        </div>
                    </form>

                    {{-- Paylaşım kısayolları. Open Graph etiketleri layout'ta
                         zaten var, paylaşılan bağlantı görsel + başlıkla çıkar. --}}
                    @include('partials.share_buttons', [
                        'shareUrl'   => route('product.detail', $product->slug),
                        'shareTitle' => $product->name,
                    ])

                    @if (in_array($product->category->slug, ['dmv-urunleri', 'lens-aksesuarlari']))
                        <!-- Sağlık & Kullanım Bilgilendirmesi Box -->
                        <div class="mt-6 bg-blue-50/50 border border-blue-200/60 rounded-xl p-4 text-xs font-sans">
                            <h4 class="text-trendyol font-extrabold flex items-center gap-1.5 mb-1.5 uppercase tracking-wide">
                                🩺 Sağlık & Kullanım Bilgilendirmesi
                            </h4>
                            <div class="space-y-1.5 text-slate-650 font-semibold leading-relaxed">
                                <p><strong>Kullanım Amacı:</strong> Bu ürün, sert gaz geçirgen (RGP) ve skleral kontakt lenslerin göze temas edilmeden takılması ve çıkarılması amacıyla tasarlanmış yardımcı bir medikal aparattır.</p>
                                <p><strong>Temizlik ve Hijyen Uyarısı:</strong> Bulaşıcı enfeksiyon riskini önlemek için her kullanım öncesi aparatı uygun lens solüsyonu ile temizleyin. Aparatı kesinlikle başkalarıyla paylaşmayın ve aşınma riskine karşı üretici talimatlarına göre düzenli aralıklarla yenileyin.</p>
                                <p class="text-slate-800 font-bold"><strong>Önemli Sağlık Notu:</strong> Ürünü kullanmadan önce mutlaka göz doktorunuza veya yetkili uygulayıcınıza danışınız. Bu ürün tıbbi bir tedavi veya teşhis yöntemi sunmaz.</p>
                            </div>
                        </div>
                    @endif

                </div>

            </div>
        </div>
    </div>

    <!-- Product Tabs (Description & Technical Specs) -->
    <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 mt-10">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8">
            
            <div class="border-b border-slate-200">
                <nav class="flex gap-6 -mb-px">
                    <button class="py-3 px-1 border-b-2 border-trendyol text-trendyol text-sm font-extrabold" id="tab-specs">Teknik Özellikler</button>
                    <button class="py-3 px-1 text-slate-500 hover:text-slate-700 text-sm font-bold transition-colors" id="tab-desc">Açıklama</button>
                </nav>
            </div>

            <!-- Specs Sheet -->
            <div class="mt-6" id="panel-specs">
                <table class="w-full text-left text-xs sm:text-sm border-collapse rounded-lg overflow-hidden border border-slate-100">
                    <tbody>
                        @if (is_array($product->features))
                            @foreach ($product->features as $key => $val)
                                <tr class="border-b border-slate-100 odd:bg-slate-50/50">
                                    <td class="px-4 py-3 font-extrabold text-slate-500 w-1/3">{{ $key }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $val }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="border-b border-slate-100">
                                <td class="px-4 py-3 text-slate-500" colspan="2">Bu ürün için teknik özellik detayları bulunmamaktadır.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Description Sheet -->
            <div class="mt-6 hidden text-sm sm:text-base leading-relaxed text-slate-650" id="panel-desc">
                <div>{!! $product->description !!}</div>
            </div>

        </div>
    </div>

    <!-- Related Products -->
    @if ($relatedProducts->count() > 0)
        <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 mt-12 mb-16">
            <div class="flex items-center gap-2 mb-6">
                <span class="w-2.5 h-6 bg-trendyol rounded-sm"></span>
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Benzer Ürünler</h2>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                @foreach ($relatedProducts as $relProduct)
                    @include('partials.product_card', ['product' => $relProduct])
                @endforeach
            </div>
        </div>
    @endif

@endsection

@section('scripts')
<script>
    // Tab Switching logic
    const tabSpecs = document.getElementById('tab-specs');
    const tabDesc = document.getElementById('tab-desc');
    const panelSpecs = document.getElementById('panel-specs');
    const panelDesc = document.getElementById('panel-desc');

    tabSpecs.addEventListener('click', () => {
        tabSpecs.classList.add('border-b-2', 'border-trendyol', 'text-trendyol', 'font-extrabold');
        tabSpecs.classList.remove('text-slate-500', 'font-bold');
        tabDesc.classList.remove('border-b-2', 'border-trendyol', 'text-trendyol', 'font-extrabold');
        tabDesc.classList.add('text-slate-500', 'font-bold');
        panelSpecs.classList.remove('hidden');
        panelSpecs.classList.add('animate-fade-in');
        panelDesc.classList.add('hidden');
    });

    tabDesc.addEventListener('click', () => {
        tabDesc.classList.add('border-b-2', 'border-trendyol', 'text-trendyol', 'font-extrabold');
        tabDesc.classList.remove('text-slate-500', 'font-bold');
        tabSpecs.classList.remove('border-b-2', 'border-trendyol', 'text-trendyol', 'font-extrabold');
        tabSpecs.classList.add('text-slate-500', 'font-bold');
        panelDesc.classList.remove('hidden');
        panelDesc.classList.add('animate-fade-in');
        panelSpecs.classList.add('hidden');
    });

    // Quantity Increment / Decrement logic
    function changeQty(amount) {
        const qtyInput = document.getElementById('quantity-input');
        if (!qtyInput) return;
        let currentVal = parseInt(qtyInput.value);
        let maxVal = parseInt(qtyInput.max) || 999;
        
        let newVal = currentVal + amount;
        if (newVal >= 1 && newVal <= maxVal) {
            qtyInput.value = newVal;
        }
    }

    // Lightbox cloning trigger
    function openLightbox() {
        const source = document.querySelector('#main-image-container');
        const target = document.getElementById('lightbox-content-target');
        if (source && target) {
            target.innerHTML = '';
            // Clone first child that holds the img or SVG
            const imageOrSvg = source.querySelector('img, svg');
            if (imageOrSvg) {
                const clone = imageOrSvg.cloneNode(true);
                // If it is an image, make sure we use the current active source
                if (imageOrSvg.tagName.toLowerCase() === 'img') {
                    const activeSrc = imageOrSvg.src;
                    clone.removeAttribute(':src'); // remove alpine binding
                    clone.src = activeSrc;
                }
                // Adjust classes on clone for maximum modal display
                clone.className = clone.className.replace(/h-\d+/g, 'h-[50vh]').replace(/w-\d+/g, 'w-[50vh]').replace(/group-hover:scale-\d+/g, '');
                target.appendChild(clone);
            }
        }
    }

    /**
     * Favori ekle/çıkar — sayfa yenilenmeden.
     *
     * Eskiden gizli bir form submit ediliyordu; sayfa başa dönünce kullanıcı
     * ürünün neresinde kaldığını kaybediyordu.
     */
    function toggleFavorite() {
        const btn = document.getElementById('fav-btn');
        if (!btn || btn.dataset.busy === '1') return;

        btn.dataset.busy = '1';

        fetch("{{ route('favorite.toggle') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                // Controller `$request->ajax()` ile JSON dönmeye bu başlıkla karar veriyor.
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ product_id: btn.dataset.product })
        })
        .then(response => response.ok ? response.json() : Promise.reject(response))
        .then(data => {
            setFavoriteState(data.status === 'added');
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        })
        .catch(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'İşlem tamamlanamadı, lütfen tekrar deneyin.',
                showConfirmButton: false,
                timer: 2500
            });
        })
        .finally(() => { btn.dataset.busy = '0'; });
    }

    /** Kalbin dolu/boş hâli ve yazısı. Fark belirgin olmalı: içi dolu pembe
     *  kalp "favorimde", çerçeveli gri kalp "değil" demek. */
    function setFavoriteState(isFav) {
        const btn   = document.getElementById('fav-btn');
        const icon  = document.getElementById('fav-icon');
        const label = document.getElementById('fav-label');

        const favClasses    = ['border-rose-200', 'bg-rose-50', 'text-rose-600'];
        const nonFavClasses = ['border-slate-200', 'text-slate-500', 'hover:border-rose-200', 'hover:text-rose-600', 'hover:bg-rose-50'];

        btn.dataset.fav = isFav ? '1' : '0';
        btn.classList.remove(...(isFav ? nonFavClasses : favClasses));
        btn.classList.add(...(isFav ? favClasses : nonFavClasses));

        icon.setAttribute('fill', isFav ? 'currentColor' : 'none');
        label.textContent = isFav ? 'Favorilerimde' : 'Favorilerime Ekle';
    }
</script>
@endsection
