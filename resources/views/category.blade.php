@extends('layouts.app')

@section('title', $category->name . ' - Buy WISEly')
@section('meta_description', $category->description ?? $category->name . ' kategorisindeki en kaliteli ürünler stoktan kargo avantajıyla.')

@section('og_type', 'website')
@section('og_title', $category->name)

{{-- Kategori kırıntısı: Google arama sonucunda URL yerine
     "Ana Sayfa › Geliştirme Kartları" şeklinde gösterir. --}}
@section('schema')
@php
    $kirintiSemasi = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => route('landing')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $category->name, 'item' => url()->current()],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($kirintiSemasi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3 font-sans">
        <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Anasayfa</a>
            <span>/</span>
            <span class="text-slate-700">{{ $category->name }}</span>
        </div>
    </div>

    <!-- Category Header -->
    <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight" id="category-title">{{ $category->name }}</h1>
            @if(isset($category->description))
                <p class="text-slate-500 text-sm mt-1.5">{{ $category->description }}</p>
            @endif

            {{-- Bu kategoriyle sınırlı arama. Üstteki genel arama tüm
                 kanalı tarıyor; ziyaretçi zaten bu kategorinin içindeyse
                 yalnızca burada aramak isteyebilir. --}}
            <form action="{{ url()->current() }}" method="GET" class="mt-4 relative max-w-md" id="category-search-form">
                @foreach (request()->except(['q', 'page']) as $anahtar => $deger)
                    <input type="hidden" name="{{ $anahtar }}" value="{{ $deger }}">
                @endforeach
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="{{ $category->name }} içinde ara..."
                    class="w-full bg-slate-50 text-slate-700 pl-4 pr-10 py-2.5 rounded-lg text-sm border border-slate-200 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white transition-all"
                    id="category-search-input"
                >
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-trendyol" id="category-search-submit">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </form>

            @if (request('q'))
                <p class="text-xs text-slate-500 font-semibold mt-2">
                    <span class="text-slate-700 font-extrabold">"{{ request('q') }}"</span> için {{ $products->total() }} sonuç bulundu
                    <a href="{{ url()->current() }}" class="text-trendyol hover:underline font-bold ml-1">(aramayı temizle)</a>
                </p>
            @endif
        </div>
    </div>

    <!-- Content Layout -->
    <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 mt-6 flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Filters -->
        <aside class="w-full lg:w-64 shrink-0">
            
            <!-- Category List -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 mb-6" id="filters-category-box">
                <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-3">Tüm Kategoriler</h3>
                <ul class="space-y-2 text-xs font-semibold">
                    @foreach ($categories as $cat)
                        <li>
                            <a 
                                href="{{ route('category', $cat->slug) }}" 
                                class="block py-1 transition-colors {{ isset($category->slug) && $category->slug === $cat->slug ? 'text-trendyol font-bold' : 'text-slate-600 hover:text-trendyol' }}"
                            >
                                {{ $cat->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Price Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5" id="filters-price-box">
                <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-3 mb-4">Fiyat Aralığı</h3>
                <form action="{{ url()->current() }}" method="GET" class="space-y-4">
                    @if (request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    @if (request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif
                    
                    <div class="flex items-center gap-2">
                        <input 
                            type="number" 
                            name="min_price" 
                            placeholder="Min TL" 
                            value="{{ request('min_price') }}"
                            class="w-full bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-xs border border-slate-200 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white"
                            id="price-filter-min"
                        >
                        <span class="text-slate-400 font-bold text-xs">-</span>
                        <input 
                            type="number" 
                            name="max_price" 
                            placeholder="Max TL" 
                            value="{{ request('max_price') }}"
                            class="w-full bg-slate-50 text-slate-700 px-3 py-2 rounded-lg text-xs border border-slate-200 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white"
                            id="price-filter-max"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-slate-800 hover:bg-slate-950 text-white py-2 rounded-lg text-xs font-bold transition-all shadow-sm"
                        id="price-filter-submit"
                    >
                        Filtrele
                    </button>
                    
                    @if (request('min_price') || request('max_price'))
                        <a 
                            href="{{ url()->current() . (request('q') ? '?q=' . request('q') : '') }}" 
                            class="block text-center text-rose-500 hover:text-rose-600 text-xs font-bold transition-colors"
                        >
                            Filtreleri Temizle
                        </a>
                    @endif
                </form>
            </div>

        </aside>

        <!-- Product Listings -->
        <section class="flex-grow">
            
            <!-- Sort Bar -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-xs sm:text-sm font-semibold text-slate-500">
                    Toplam <span class="text-slate-900 font-extrabold">{{ $products->total() }}</span> ürün listelendi
                </span>

                <div class="flex items-center gap-2">
                    <label for="sort-select" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sırala:</label>
                    <select 
                        id="sort-select"
                        onchange="location = this.value;"
                        class="bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-700 py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white"
                    >
                        @php 
                            $queryParams = request()->except(['sort', 'page']);
                            $buildSortUrl = function($sortType) use ($queryParams) {
                                return url()->current() . '?' . http_build_query(array_merge($queryParams, ['sort' => $sortType]));
                            };
                        @endphp
                        <option value="{{ $buildSortUrl('popular') }}" {{ request('sort') === 'popular' || !request('sort') ? 'selected' : '' }}>En Popülerler</option>
                        <option value="{{ $buildSortUrl('price_asc') }}" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Düşük Fiyat</option>
                        <option value="{{ $buildSortUrl('price_desc') }}" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Yüksek Fiyat</option>
                        <option value="{{ $buildSortUrl('rating') }}" {{ request('sort') === 'rating' ? 'selected' : '' }}>En Yüksek Puanlılar</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            @if ($products->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($products as $prod)
                        @include('partials.product_card', ['product' => $prod])
                    @endforeach
                </div>

                <!-- Custom Elegant Pagination -->
                @if ($products->hasPages())
                    <div class="mt-12 flex justify-center">
                        <div class="flex items-center gap-1.5 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-100">
                            {{-- Previous Page Link --}}
                            @if ($products->onFirstPage())
                                <span class="p-2 text-slate-300 cursor-not-allowed">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                                </span>
                            @else
                                <a href="{{ $products->previousPageUrl() }}" class="p-2 text-slate-600 hover:text-trendyol hover:bg-slate-50 rounded-lg transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                                </a>
                            @endif

                            {{-- Numeric Links --}}
                            @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                                @if ($page == $products->currentPage())
                                    <span class="w-8 h-8 flex items-center justify-center bg-trendyol text-white rounded-lg text-xs font-bold shadow-sm">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-slate-600 hover:text-trendyol hover:bg-slate-50 rounded-lg text-xs font-bold transition-all">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($products->hasMorePages())
                                <a href="{{ $products->nextPageUrl() }}" class="p-2 text-slate-600 hover:text-trendyol hover:bg-slate-50 rounded-lg transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            @else
                                <span class="p-2 text-slate-300 cursor-not-allowed">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-sm font-extrabold text-slate-900 mb-1">Ürün Bulunamadı</h3>
                    <p class="text-xs text-slate-500 max-w-xs mx-auto">Seçtiğiniz kriterlere veya arama terimine uygun ürün stoklarımızda bulunmamaktadır.</p>
                </div>
            @endif

        </section>

    </div>

@endsection
