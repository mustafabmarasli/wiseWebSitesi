@extends('layouts.app')

@section('title', $channelTitle . ' - Buy WISEly')

@section('meta_description', 'En kaliteli ' . $channelTitle . ' ürünleri, geliştirme kartları, lens saklama kutuları ve DMV vantuz aparatları en uygun fiyat ve hızlı teslimatla sitemizde!')

{{-- Duyuru penceresi — yalnızca mağaza sayfalarında (Elektronik / Sağlık),
     ana portal sayfasında değil. Panelden açılıp kapatılabilir.

     ÖNEMLİ: İçerik bölümüne değil `modals` bölümüne konur. <main> öğesinde
     `animate-fade-in` animasyonu bir `transform` üretiyor; bu da içindeki
     position:fixed öğeleri viewport yerine <main>'e göre konumlandırıyor
     ve pencere sayfanın çok aşağısında kalıyordu. --}}
@section('modals')
    @include('partials.announcement')
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 font-sans">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sol Menü: Kategoriler (Left Sidebar) -->
        <aside class="w-full lg:w-64 shrink-0">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 sticky top-6">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                    <span class="w-2.5 h-4 bg-trendyol rounded-sm"></span>
                    <h3 class="text-sm font-extrabold text-slate-900">Kategoriler</h3>
                </div>
                <ul class="space-y-1.5 text-xs font-semibold">
                    @foreach ($categories as $cat)
                        <li>
                            <a 
                                href="{{ route('category', $cat->slug) }}" 
                                class="block py-2 px-2.5 rounded-xl text-slate-650 hover:bg-slate-50 hover:text-trendyol transition-all"
                            >
                                {{ $cat->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <!-- Sağ İçerik: Banner + Ürün Listeleri (Right Content) -->
        <div class="flex-1 min-w-0 space-y-4">
            
            <!-- Hero Slider (Dynamic Banner) -->
            <div class="relative rounded-2xl overflow-hidden shadow-lg border border-slate-200 group h-48 sm:h-80 md:h-[400px]">
                <!-- Slides Container -->
                <div id="hero-slider" class="flex transition-transform duration-700 ease-in-out h-full w-full" style="transform: translateX(0%);">
                    @if ($channel === 'health')
                        <!-- Slayt 1 (Sağlık) -->
                        <div class="w-full h-full shrink-0 relative">
                            <img src="{{ asset('images/banner_health.png') }}" alt="Genel Sağlık Kampanyası" class="w-full h-full object-cover" loading="eager">
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 to-transparent flex flex-col justify-center px-8 sm:px-16 text-white">
                                <span class="bg-trendyol text-white font-extrabold text-xs uppercase tracking-widest px-3 py-1 rounded-full w-max mb-3">Sağlık & Medikal</span>
                                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black max-w-lg leading-tight">Kontakt Lens Aksesuarları</h1>
                                <p class="text-slate-300 text-xs sm:text-base mt-2 max-w-sm">Yetkili satıcısı olduğumuz orijinal DMV® vantuzları ve sızdırmaz lens kapları.</p>
                                <div class="mt-6 flex gap-4">
                                    <a href="#tum-urunler" class="bg-trendyol hover:bg-trendyolDark text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition-all">Ürünleri Gör</a>
                                    <a href="{{ route('contact') }}" class="bg-white/10 hover:bg-white/20 text-white backdrop-blur border border-white/30 px-6 py-2.5 rounded-lg text-sm font-bold transition-all">Danışın</a>
                                </div>
                            </div>
                        </div>
                        <!-- Slayt 2 (Sağlık) -->
                        <div class="w-full h-full shrink-0 relative">
                            <img src="{{ asset('images/banner_health2.png') }}" alt="Orijinal DMV Aparatları" class="w-full h-full object-cover" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 to-transparent flex flex-col justify-center px-8 sm:px-16 text-white">
                                <span class="bg-amber-500 text-white font-extrabold text-xs uppercase tracking-widest px-3 py-1 rounded-full w-max mb-3">Amerika'dan İthal</span>
                                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black max-w-lg leading-tight">Orijinal DMV® Ürünleri</h1>
                                <p class="text-slate-300 text-xs sm:text-base mt-2 max-w-sm">Skleral, sert ve yumuşak lensler için patentli takma ve çıkarma vantuzları.</p>
                                <div class="mt-6 flex gap-4">
                                    <a href="#tum-urunler" class="bg-trendyol hover:bg-trendyolDark text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition-all">Şimdi İncele</a>
                                    <a href="{{ route('contact') }}" class="bg-white/10 hover:bg-white/20 text-white backdrop-blur border border-white/30 px-6 py-2.5 rounded-lg text-sm font-bold transition-all">Bilgi Al</a>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Slayt 1 (Elektronik) -->
                        <div class="w-full h-full shrink-0 relative">
                            <img src="{{ asset('images/banner.png') }}" alt="Elektronik Geliştirme Kartları Kampanyası" class="w-full h-full object-cover" loading="eager">
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 to-transparent flex flex-col justify-center px-8 sm:px-16 text-white">
                                <span class="bg-trendyol text-white font-extrabold text-xs uppercase tracking-widest px-3 py-1 rounded-full w-max mb-3">Gelişmiş Donanım</span>
                                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black max-w-lg leading-tight">Mikroişlemci Geliştirme Kartları</h1>
                                <p class="text-slate-300 text-xs sm:text-base mt-2 max-w-sm">ESP32, ESP8266, S Tipi LED ve COB LED aydınlatma bileşenleri stoktan teslim.</p>
                                <div class="mt-6 flex gap-4">
                                    <a href="#tum-urunler" class="bg-trendyol hover:bg-trendyolDark text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition-all">Şimdi Keşfet</a>
                                    <a href="{{ route('contact') }}" class="bg-white/10 hover:bg-white/20 text-white backdrop-blur border border-white/30 px-6 py-2.5 rounded-lg text-sm font-bold transition-all">Destek Al</a>
                                </div>
                            </div>
                        </div>
                        <!-- Slayt 2 (Elektronik) -->
                        <div class="w-full h-full shrink-0 relative">
                            <img src="{{ asset('images/banner2.png') }}" alt="COB LED Aydınlatma Teknolojisi" class="w-full h-full object-cover" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 to-transparent flex flex-col justify-center px-8 sm:px-16 text-white">
                                <span class="bg-emerald-500 text-white font-extrabold text-xs uppercase tracking-widest px-3 py-1 rounded-full w-max mb-3">Yeni Nesil Işık</span>
                                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black max-w-lg leading-tight">COB & S-Tipi LED Teknolojisi</h1>
                                <p class="text-slate-300 text-xs sm:text-base mt-2 max-w-sm">Noktasız kesintisiz ışık veren şerit LED'ler ve bükülebilir S-Tipi aydınlatmalar.</p>
                                <div class="mt-6 flex gap-4">
                                    <a href="#tum-urunler" class="bg-trendyol hover:bg-trendyolDark text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition-all">Modelleri Gör</a>
                                    <a href="{{ route('contact') }}" class="bg-white/10 hover:bg-white/20 text-white backdrop-blur border border-white/30 px-6 py-2.5 rounded-lg text-sm font-bold transition-all">Detaylı Bilgi</a>
                                </div>
                            </div>
                        </div>
                        <!-- Slayt 3 (Elektronik) -->
                        <div class="w-full h-full shrink-0 relative">
                            <img src="{{ asset('images/banner3.png') }}" alt="IoT Projeleri" class="w-full h-full object-cover" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 to-transparent flex flex-col justify-center px-8 sm:px-16 text-white">
                                <span class="bg-blue-500 text-white font-extrabold text-xs uppercase tracking-widest px-3 py-1 rounded-full w-max mb-3">Geleceğin Teknolojisi</span>
                                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black max-w-lg leading-tight">IoT Projenize Özel Çözüm</h1>
                                <p class="text-slate-300 text-xs sm:text-base mt-2 max-w-sm">Akıllı ev sistemleri, giyilebilir teknoloji ve sensör modülleriyle projeler üretin.</p>
                                <div class="mt-6 flex gap-4">
                                    <a href="#tum-urunler" class="bg-trendyol hover:bg-trendyolDark text-white px-6 py-2.5 rounded-lg text-sm font-bold shadow-md hover:shadow-lg transition-all">Projelere Başla</a>
                                    <a href="{{ route('contact') }}" class="bg-white/10 hover:bg-white/20 text-white backdrop-blur border border-white/30 px-6 py-2.5 rounded-lg text-sm font-bold transition-all">İletişime Geç</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Dots Indicators -->
                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2 z-20">
                    @if ($channel === 'health')
                        <button onclick="goToSlide(0)" class="w-2.5 h-2.5 rounded-full bg-white/50 hover:bg-white transition-all slider-dot" id="dot-0"></button>
                        <button onclick="goToSlide(1)" class="w-2.5 h-2.5 rounded-full bg-white/50 hover:bg-white transition-all slider-dot" id="dot-1"></button>
                    @else
                        <button onclick="goToSlide(0)" class="w-2.5 h-2.5 rounded-full bg-white/50 hover:bg-white transition-all slider-dot" id="dot-0"></button>
                        <button onclick="goToSlide(1)" class="w-2.5 h-2.5 rounded-full bg-white/50 hover:bg-white transition-all slider-dot" id="dot-1"></button>
                        <button onclick="goToSlide(2)" class="w-2.5 h-2.5 rounded-full bg-white/50 hover:bg-white transition-all slider-dot" id="dot-2"></button>
                    @endif
                </div>

                <!-- Slider Arrows -->
                <button onclick="moveSlide(-1)" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/30 hover:bg-black/55 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity z-20 focus:outline-none hidden sm:block">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button onclick="moveSlide(1)" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/30 hover:bg-black/55 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity z-20 focus:outline-none hidden sm:block">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>

            <!-- Neden Biz? Features Strip -->
            <div class="flex flex-wrap items-center justify-between gap-2 bg-white border border-slate-100 px-4 py-3 rounded-2xl shadow-sm">
                <div class="flex items-center gap-2">
                    <div class="bg-emerald-50 text-emerald-600 p-1.5 rounded-lg shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-extrabold text-slate-800">Ücretsiz Kargo</p>
                        <p class="text-[10px] text-slate-400 font-medium">Tüm siparişlerde</p>
                    </div>
                </div>
                <div class="w-px h-8 bg-slate-100 hidden sm:block"></div>
                <div class="flex items-center gap-2">
                    <div class="bg-indigo-50 text-indigo-600 p-1.5 rounded-lg shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-extrabold text-slate-800">Güvenli Ödeme</p>
                        <p class="text-[10px] text-slate-400 font-medium">iyzico &amp; 256-bit SSL</p>
                    </div>
                </div>
                <div class="w-px h-8 bg-slate-100 hidden sm:block"></div>
                <div class="flex items-center gap-2">
                    <div class="bg-rose-50 text-rose-600 p-1.5 rounded-lg shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H18.78"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-extrabold text-slate-800">Kolay İade</p>
                        <p class="text-[10px] text-slate-400 font-medium">14 gün garantisi</p>
                    </div>
                </div>
                <div class="w-px h-8 bg-slate-100 hidden sm:block"></div>
                <div class="flex items-center gap-2">
                    <div class="bg-amber-50 text-amber-600 p-1.5 rounded-lg shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-extrabold text-slate-800">Teknik Destek</p>
                        <p class="text-[10px] text-slate-400 font-medium">Satış öncesi &amp; sonrası</p>
                    </div>
                </div>
            </div>


            <!-- DMV Yetkili Satıcısı Rozeti -->
            @if ($channel === 'health')
                <div class="bg-gradient-to-r from-emerald-50/60 via-teal-50/40 to-emerald-50/60 border border-emerald-200/70 rounded-2xl px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm font-sans">
                    <div class="flex items-center gap-4">
                        <div class="bg-emerald-500 text-white p-2.5 rounded-xl shrink-0 shadow-md">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-extrabold text-emerald-700 uppercase tracking-widest mb-0.5">Resmi Distribütör</p>
                            <h3 class="text-base sm:text-lg font-black text-slate-900"><span class="text-xl sm:text-2xl font-black text-slate-900 mr-1.5">US</span>DMV Corporation — Türkiye Yetkili Satıcısı</h3>
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">Tüm DMV ürünleri orijinal ABD imalatıdır. Sahte ürün riskine karşı yetkili satıcıdan alın.</p>
                        </div>
                    </div>
                    <div class="shrink-0 bg-white border border-emerald-200 rounded-xl px-4 py-2 text-center shadow-sm">
                        <p class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">Orijinallik</p>
                        <p class="text-sm font-bold text-emerald-600">%100 Orijinal</p>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-2 font-medium">
                    * DMV aparatlarını kullanırken göz doktorunuzun tavsiyelerine ve kullanım kılavuzuna uymanız gerekmektedir. Ürünlerimiz tıbbi bir teşhis veya tedavi aracı değildir.
                </p>
            @endif

            {{-- ===== VİTRİN ÜRÜNLER (2 Kart) ===== --}}
            @if ($showcaseProducts->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($showcaseProducts as $i => $feat)
                @php
                    $gradients = [
                        'from-slate-900 via-slate-800 to-indigo-950',
                        'from-slate-900 via-slate-800 to-rose-950',
                    ];
                    $accents = ['#6366F1', '#F43F5E'];
                    $grad = $gradients[$i % 2];
                    $accent = $accents[$i % 2];
                @endphp
                <div class="relative rounded-2xl overflow-hidden border border-white/5 shadow-lg bg-gradient-to-br {{ $grad }} group">
                    {{-- Arkaplan ışık --}}
                    <div class="absolute inset-0 opacity-20" style="background: radial-gradient(circle at 80% 50%, {{ $accent }}55 0%, transparent 60%);"></div>

                    <div class="relative z-10 flex items-center gap-4 p-5">
                        {{-- Görsel --}}
                        <div class="shrink-0 w-20 h-20 sm:w-24 sm:h-24 flex items-center justify-center">
                            @if ($feat->image_url)
                                <img src="{{ $feat->image_url }}" alt="{{ $feat->name }}"
                                     class="w-full h-full object-contain drop-shadow-lg transition-transform duration-500 group-hover:scale-110">
                            @else
                                <div class="w-full h-full bg-white/5 rounded-xl flex items-center justify-center border border-white/10">
                                    <svg class="h-8 w-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Bilgi --}}
                        <div class="flex-1 min-w-0">
                            <span class="inline-flex items-center gap-1 text-[9px] font-extrabold uppercase tracking-widest mb-1.5" style="color: {{ $accent }};">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" style="background:{{ $accent }};"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5" style="background:{{ $accent }};"></span>
                                </span>
                                Vitrin Ürün
                            </span>
                            <h3 class="text-sm font-extrabold text-white leading-snug line-clamp-2 mb-2">{{ $feat->name }}</h3>

                            {{-- Fiyat --}}
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-base font-black text-white">{{ number_format($feat->price, 2, ',', '.') }} ₺</span>
                                @if ($feat->eski_fiyat && $feat->eski_fiyat > $feat->price)
                                    @php $pct = round((($feat->eski_fiyat - $feat->price) / $feat->eski_fiyat) * 100); @endphp
                                    <span class="text-[10px] font-extrabold text-white px-1.5 py-0.5 rounded-full" style="background:{{ $accent }};">%{{ $pct }}</span>
                                @endif
                            </div>

                            {{-- Butonlar --}}
                            <div class="flex gap-2">
                                <a href="{{ route('product.detail', $feat->slug) }}"
                                   class="flex-1 text-center text-[11px] font-extrabold text-white py-1.5 rounded-lg transition-all hover:opacity-90 active:scale-95"
                                   style="background: {{ $accent }};">
                                    İncele
                                </a>
                                {{-- Stoğu bitmiş ürüne "+ Sepet" göstermek yanıltıcıydı:
                                     buton tıklanıyor ama sunucu haklı olarak reddediyordu. --}}
                                @if ($feat->stock <= 0)
                                    <button type="button" onclick="notifyStock({{ $feat->id }})"
                                        class="flex-1 text-[11px] font-bold text-white/60 py-1.5 rounded-lg border border-white/20 hover:bg-white/10 transition-all active:scale-95">
                                        Tükendi · Haber Ver
                                    </button>
                                @else
                                    <form action="{{ route('cart.add') }}" method="POST" class="m-0 flex-1">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $feat->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit"
                                            class="w-full text-[11px] font-bold text-white/80 py-1.5 rounded-lg border border-white/20 hover:bg-white/10 transition-all active:scale-95">
                                            + Sepet
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            {{-- ===== / VİTRİN ÜRÜNLER ===== --}}


            <!-- Tüm Ürünler Section -->
            <div id="tum-urunler">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-6 bg-trendyol rounded-sm"></span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Tüm Ürünler</h2>
                    </div>
                    <span class="text-xs sm:text-sm text-slate-500 font-medium">Geniş stok, hızlı gönderi</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($allProducts as $product)
                        @include('partials.product_card', ['product' => $product])
                    @endforeach
                </div>
            </div>

            <!-- Popüler Ürünler Section -->
            @if ($popularProducts->count() > 0)
            <div class="bg-slate-50/50 py-10 px-6 rounded-3xl border border-slate-100 font-sans">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-6 bg-trendyol rounded-sm"></span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Popüler Ürünler</h2>
                    </div>
                    <span class="text-xs sm:text-sm text-trendyol font-extrabold uppercase tracking-wider bg-slate-100 px-3 py-1 rounded-full">En Çok Satanlar</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($popularProducts as $product)
                        @include('partials.product_card', ['product' => $product, 'isPopular' => true])
                    @endforeach
                </div>
            </div>
            @endif

            <!-- İndirimli Ürünler Section -->
            @if ($discountedProducts->count() > 0)
            <div class="bg-orange-50/50 py-10 px-6 rounded-3xl border border-orange-100/50 font-sans">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-6 bg-trendyol rounded-sm"></span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">İndirimli Ürünler</h2>
                    </div>
                    <span class="text-xs sm:text-sm text-trendyol font-extrabold uppercase tracking-wider bg-orange-100 px-3 py-1 rounded-full">Kaçırılmayacak Fiyatlar</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                    @foreach ($discountedProducts as $product)
                        @include('partials.product_card', ['product' => $product, 'isDiscount' => true])
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

    <script>
        let currentSlide = 0;
        const slidesCount = {{ $channel === 'health' ? 2 : 3 }};
        const sliderEl = document.getElementById('hero-slider');
        
        function updateSlider() {
            sliderEl.style.transform = `translateX(-${currentSlide * 100}%)`;
            
            document.querySelectorAll('.slider-dot').forEach((dot, idx) => {
                if (idx === currentSlide) {
                    dot.classList.add('bg-white', 'w-5');
                    dot.classList.remove('bg-white/50');
                } else {
                    dot.classList.remove('bg-white', 'w-5');
                    dot.classList.add('bg-white/50');
                }
            });
        }

        function goToSlide(slideIndex) {
            currentSlide = slideIndex;
            updateSlider();
        }

        function moveSlide(direction) {
            currentSlide = (currentSlide + direction + slidesCount) % slidesCount;
            updateSlider();
        }

        let autoSlideInterval = setInterval(() => {
            moveSlide(1);
        }, 5000);

        document.querySelectorAll('button, .slider-dot').forEach(el => {
            el.addEventListener('click', () => {
                clearInterval(autoSlideInterval);
                autoSlideInterval = setInterval(() => {
                    moveSlide(1);
                }, 5000);
            });
        });

        // Touch Swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        const sliderContainer = document.querySelector('.relative.rounded-2xl.overflow-hidden.shadow-lg');
        
        if (sliderContainer) {
            sliderContainer.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            sliderContainer.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                handleGesture();
            }, { passive: true });
        }

        function handleGesture() {
            const swipeThreshold = 50;
            if (touchStartX - touchEndX > swipeThreshold) {
                // Swipe left -> next slide
                clearInterval(autoSlideInterval);
                moveSlide(1);
                restartAutoSlide();
            } else if (touchEndX - touchStartX > swipeThreshold) {
                // Swipe right -> prev slide
                clearInterval(autoSlideInterval);
                moveSlide(-1);
                restartAutoSlide();
            }
        }

        function restartAutoSlide() {
            clearInterval(autoSlideInterval);
            autoSlideInterval = setInterval(() => {
                moveSlide(1);
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateSlider();
        });
    </script>
@endsection
