<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- SEO Optimization -->
    <link rel="icon" type="image/jpeg" href="{{ asset('img/strong-modern-logo-for--wise-solutions---large-bol (1).jpg') }}">
    <title>@yield('title', 'Buy WISEly - Geliştirme Kartları ve Sağlık Ürünleri')</title>
    <meta name="description" content="@yield('meta_description', 'Arduino, Raspberry Pi, ESP32 geliştirme kartları, sensörler ve robotik malzemeler en uygun fiyatlarla sitemizde!')">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Sosyal paylaşım kartları (WhatsApp, Facebook, X, LinkedIn).
         Bunlar olmadan paylaşılan link çıplak metin olarak görünür. --}}
    <meta property="og:site_name" content="Buy WISEly">
    <meta property="og:locale" content="tr_TR">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', View::yieldContent('title', 'Buy WISEly'))">
    <meta property="og:description" content="@yield('og_description', View::yieldContent('meta_description', 'Geliştirme kartları, sensörler ve lens aksesuarları.'))">
    <meta property="og:image" content="@yield('og_image', asset('images/banner.png'))">
    <meta property="og:image:width" content="@yield('og_image_width', '1200')">
    <meta property="og:image:height" content="@yield('og_image_height', '630')">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', View::yieldContent('title', 'Buy WISEly'))">
    <meta name="twitter:description" content="@yield('og_description', View::yieldContent('meta_description', 'Geliştirme kartları, sensörler ve lens aksesuarları.'))">
    <meta name="twitter:image" content="@yield('og_image', asset('images/banner.png'))">

    {{-- Yapısal veri: her sayfa kendi şemasını `schema` bölümüne basar.
         DİKKAT: Puanlar gerçek müşteri yorumu değil (seed verisi), bu yüzden
         aggregateRating ASLA eklenmemeli — Google sahte değerlendirme sayar. --}}
    @php
        // Not: @json() direktifi çok satırlı iç içe dizilerde derlenen PHP'yi
        // bozuyor (ParseError). Dizi burada kurulup json_encode ile basılır.
        $kurumSemasi = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => 'Wise Solutions',
            'url'      => url('/'),
            'logo'     => asset('img/strong-modern-logo-for--wise-solutions---large-bol (1).jpg'),
            'email'    => config('mail.admin_address'),
            'address'  => [
                '@type'           => 'PostalAddress',
                'addressLocality' => 'Kayseri',
                'addressCountry'  => 'TR',
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($kurumSemasi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @yield('schema')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    {{-- SweetAlert2 — sürüm SABİT + SRI.
         Sürüm aralığı (@11) kullanılırsa CDN yeni yama yayınladığında hash
         tutmaz ve script hiç yüklenmez. Sürümü yükseltirken integrity
         değerini de yenilemek gerekir. --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25/dist/sweetalert2.all.min.js"
            integrity="sha384-nLoOnA/BDh8A/jxqtckg4DumuCGOBYUnNJLZdQz/zfYNp3wcjGSoWTAzgko06G/2"
            crossorigin="anonymous"
            referrerpolicy="no-referrer"></script>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        trendyol: '#1B4A7A', // Modern Koyu Lacivert
                        trendyolDark: '#14385C',
                        accentTeal: '#2DD4BF', // Aksan Teal Yeşil
                        accentAmber: '#F59E0B', // Aksan Amber
                        darkNavy: '#0F172A',
                        cardBg: '#FAFAFA'
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #F8FAFC;
        }
        .trendyol-border-active {
            border-bottom: 3px solid #F27A1A;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        * {
            -webkit-tap-highlight-color: transparent;
        }
    </style>
    
    <!-- AlpineJS CDN -->
    {{-- Alpine.js — sürüm SABİT + SRI (yukarıdaki SweetAlert notuna bakın) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.12/dist/cdn.min.js"
            integrity="sha384-pb6hrQvo4s23cEUFtj0CZkzGE3jyK3pj26RIupXXxhSrrcUA/Cn0lZgcCrGH0t6L"
            crossorigin="anonymous"
            referrerpolicy="no-referrer"></script>

    @yield('styles')
</head>
<body class="min-h-screen flex flex-col text-slate-800 pb-16 md:pb-0" x-data="{ mobileMenuOpen: false, profileDropdownOpen: false }">

    @php
        $currentPath = request()->path();
        $danismanlikAcik = \App\Models\Setting::current()->consulting_enabled;
        $activeChannel = 'electronics';
        
        // Define active channel logic
        if (Str::contains($currentPath, 'saglik') || 
            (isset($category) && $category->channel === 'health') ||
            (isset($product) && isset($product->category) && $product->category->channel === 'health')) {
            $activeChannel = 'health';
        }
        
        if ($activeChannel === 'health') {
            $headerCategories = \App\Models\Category::where('channel', 'health')->get();
            $shopHomeRoute = route('health.home');
            $shopTitle = 'Sağlık & Lens';
        } else {
            $headerCategories = \App\Models\Category::where('channel', 'electronics')->get();
            $shopHomeRoute = route('electronics.home');
            $shopTitle = 'Elektronik';
        }
    @endphp

    <!-- Top Channel Selector Bar (Trendyol Style) -->
    <div class="bg-slate-100 border-b border-slate-200 py-2 text-xs font-semibold text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex gap-4">
                <a href="{{ route('electronics.home') }}" class="hover:text-trendyol pb-0.5 {{ $activeChannel === 'electronics' && !Str::contains($currentPath, 'danismanlik') ? 'text-trendyol font-extrabold border-b-2 border-trendyol' : '' }}">Elektronik</a>
                <span class="text-slate-300">|</span>
                <a href="{{ route('health.home') }}" class="hover:text-trendyol pb-0.5 {{ $activeChannel === 'health' ? 'text-trendyol font-extrabold border-b-2 border-trendyol' : '' }}">Sağlık & Lens</a>
                @if ($danismanlikAcik)
                    <span class="text-slate-300">|</span>
                    <a href="{{ route('consulting') }}" class="hover:text-trendyol pb-0.5 {{ Str::contains($currentPath, 'danismanlik') ? 'text-trendyol font-extrabold border-b-2 border-trendyol' : '' }}">Danışmanlık & Dış Ticaret</a>
                @endif
            </div>
            <div class="hidden sm:block">
                <a href="{{ route('landing') }}" class="hover:text-trendyol flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    Ana Giriş Portalı
                </a>
            </div>
        </div>
    </div>

    <!-- Top Navigation Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20 gap-4">
                
                <!-- Logo -->
                <a href="{{ $shopHomeRoute }}" class="flex items-center shrink-0 gap-2.5" id="header-logo-link">
                    <img src="{{ asset('img/strong-modern-logo-for--wise-solutions---large-bol (1).jpg') }}" alt="Wise Solutions Logo" class="h-10 sm:h-12 w-auto object-contain rounded-lg bg-white border border-slate-200 p-0.5 shadow-sm">
                    <div class="flex flex-col leading-none">
                        <span class="text-lg sm:text-xl font-black text-trendyol tracking-tight">Wise</span>
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-widest">Solutions</span>
                    </div>
                </a>
                
                <!-- Search Bar -->
                <div class="flex-1 max-w-xl mx-4 hidden md:block">
                    <form action="{{ route('product.search') }}" method="GET" class="relative" id="global-search-form">
                        <input type="hidden" name="channel" value="{{ $activeChannel }}">
                        <input 
                            type="text" 
                            name="q" 
                            placeholder="{{ $activeChannel === 'health' ? 'Lens kutusu, DMV vantuz veya aparat arayın...' : 'ESP32, LED şerit veya kart arayın...' }}" 
                            value="{{ request('q') }}"
                            class="w-full bg-slate-100 text-slate-700 pl-4 pr-10 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white transition-all text-sm border border-transparent"
                            id="search-input-desktop"
                            required
                        >
                        <button type="submit" class="absolute right-3 top-2.5 text-slate-400 hover:text-trendyol" id="search-btn-desktop">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </form>
                </div>
                
                <!-- Icons/Actions -->
                <div class="flex items-center gap-4 sm:gap-6">
                    <a href="{{ route('contact') }}" class="text-slate-600 hover:text-trendyol flex flex-col items-center text-xs font-medium" id="header-contact-link">
                        <svg class="h-6 w-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span>İletişim</span>
                    </a>
                    
                    @auth
                        <!-- Profile Dropdown -->
                        <div class="relative flex flex-col items-center cursor-pointer" @click.away="profileDropdownOpen = false">
                            <button @click="profileDropdownOpen = !profileDropdownOpen" class="text-slate-600 hover:text-trendyol flex flex-col items-center text-xs font-medium focus:outline-none" id="header-profile-btn">
                                <svg class="h-6 w-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="max-w-[70px] truncate">{{ Auth::user()->name }}</span>
                            </button>
                            <div x-show="profileDropdownOpen"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 top-full mt-2 w-48 bg-white border border-slate-100 rounded-xl shadow-xl py-2 z-[9999]"
                                 style="display: none;">
                                <span class="block px-4 py-1.5 text-[10px] font-extrabold text-slate-400 border-b border-slate-50 uppercase truncate">{{ Auth::user()->email }}</span>
                                <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 font-bold transition-all mt-1">
                                    Hesabım (Profil)
                                </a>
                                <a href="{{ route('profile.favorites') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 font-bold transition-all">
                                    Favorilerim
                                </a>
                                <a href="{{ route('profile.orders') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-slate-50 font-bold transition-all">
                                    Siparişlerim
                                </a>
                                <div class="border-t border-slate-50 my-1"></div>
                                <form action="{{ route('logout') }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs text-rose-600 hover:bg-slate-50 font-bold transition-all">
                                        Çıkış Yap
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-slate-600 hover:text-trendyol flex flex-col items-center text-xs font-medium" id="header-login-link">
                            <svg class="h-6 w-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>Giriş / Kayıt</span>
                        </a>
                    @endauth

                    <a href="{{ route('cart.index') }}" class="text-slate-600 hover:text-trendyol flex flex-col items-center text-xs font-medium relative" id="header-cart-link">
                        <svg class="h-6 w-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Sepetim</span>
                        @php $cartCount = count(session()->get('cart', [])); @endphp
                        @if ($cartCount > 0)
                            <span class="absolute -top-1.5 -right-2 bg-trendyol text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center border border-white" id="cart-badge-count">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>

                    <!-- Hamburger Menu Button (Only on Mobile) -->
                    <button @click="mobileMenuOpen = true" class="md:hidden text-slate-600 hover:text-trendyol flex flex-col items-center justify-center text-xs font-medium focus:outline-none" id="header-menu-btn">
                        <svg class="h-6 w-6 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <span>Menü</span>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Search Bar (Only shown on small screens) -->
            <div class="pb-4 block md:hidden">
                <form action="{{ route('product.search') }}" method="GET" class="relative" id="mobile-search-form">
                    <input type="hidden" name="channel" value="{{ $activeChannel }}">
                    <input 
                        type="text" 
                        name="q" 
                        placeholder="{{ $activeChannel === 'health' ? 'Lens kutusu, DMV vantuz arayın...' : 'ESP32, LED şerit arayın...' }}" 
                        value="{{ request('q') }}"
                        class="w-full bg-slate-100 text-slate-700 pl-4 pr-10 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white transition-all text-sm border border-transparent"
                        id="search-input-mobile"
                        required
                    >
                    <button type="submit" class="absolute right-3 top-2 text-slate-400 hover:text-trendyol" id="search-btn-mobile">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>


    </header>

    <!-- Main Content Area -->
    <main class="flex-grow animate-fade-in">
        @yield('content')
    </main>

    <!-- Footer Area -->
    <footer class="bg-slate-900 text-slate-300 pt-16 pb-8 mt-16 border-t border-slate-800 font-sans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            
            <!-- Company Info & Phone/WhatsApp -->
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <img src="{{ asset('img/strong-modern-logo-for--wise-solutions---large-bol (1).jpg') }}" alt="Wise Solutions Logo" class="h-9 w-auto object-contain rounded bg-white p-0.5 shadow-sm">
                    <div class="flex flex-col leading-none">
                        <span class="text-base font-black text-white tracking-tight">Wise</span>
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Solutions</span>
                    </div>
                </div>
                <p class="text-xs text-slate-455 mb-5 leading-relaxed">Elektronik donanım ve IoT bileşenlerinden, medikal kontakt lens vantuzlarına ve dış ticaret danışmanlığına kadar geniş bir kapsamda hizmet sunmaktayız.</p>
                <div class="space-y-1.5 text-xs text-slate-400">
                    <p class="flex items-center gap-2"><span class="text-slate-500">📧</span> info@wisesolutions.com.tr</p>
                    <p class="flex items-center gap-2"><span class="text-slate-500">🌐</span> www.wisesolutions.com.tr</p>
                </div>
            </div>

            <!-- Shop Categories -->
            <div>
                <h3 class="text-white text-sm font-extrabold mb-4 uppercase tracking-wider">Mağaza Kanalları</h3>
                <ul class="space-y-4 text-xs font-bold text-slate-400">
                    <li><a href="{{ route('electronics.home') }}" class="hover:text-accentTeal hover:translate-x-1 duration-200 transition-all inline-block py-1">Elektronik Mağazası</a></li>
                    <li><a href="{{ route('health.home') }}" class="hover:text-accentTeal hover:translate-x-1 duration-200 transition-all inline-block py-1">Sağlık & Lens Mağazası</a></li>
                    @if ($danismanlikAcik)
                    <li><a href="{{ route('consulting') }}" class="hover:text-accentTeal hover:translate-x-1 duration-200 transition-all inline-block py-1">Danışmanlık Hizmetleri</a></li>
                    @endif
                    <li><a href="{{ route('contact') }}" class="hover:text-accentTeal hover:translate-x-1 duration-200 transition-all inline-block py-1">Müşteri İletişimi</a></li>
                </ul>
            </div>

            <!-- Corporate / Procedures -->
            <div>
                <h3 class="text-white text-sm font-extrabold mb-4 uppercase tracking-wider">Kurumsal & Yasal</h3>
                <ul class="space-y-4 text-xs font-bold text-slate-400" id="footer-procedural-links">
                    <li><a href="{{ route('procedural', 'mesafeli-satis') }}" class="hover:text-accentTeal hover:translate-x-1 duration-200 transition-all inline-block py-1">Mesafeli Satış Sözleşmesi</a></li>
                    <li><a href="{{ route('procedural', 'on-bilgilendirme') }}" class="hover:text-accentTeal hover:translate-x-1 duration-200 transition-all inline-block py-1">Ön Bilgilendirme Formu</a></li>
                    <li><a href="{{ route('kvkk') }}" class="hover:text-accentTeal hover:translate-x-1 duration-200 transition-all inline-block py-1">KVKK Aydınlatma Metni</a></li>
                    <li><a href="{{ route('procedural', 'gizlilik-guvenlik') }}" class="hover:text-accentTeal hover:translate-x-1 duration-200 transition-all inline-block py-1">Gizlilik ve Güvenlik</a></li>
                    <li><a href="{{ route('procedural', 'cerez-politikasi') }}" class="hover:text-accentTeal hover:translate-x-1 duration-200 transition-all inline-block py-1">Çerez Politikası</a></li>
                    <li><a href="{{ route('procedural', 'teslimat-iade') }}" class="hover:text-accentTeal hover:translate-x-1 duration-200 transition-all inline-block py-1">Teslimat ve İade Koşulları</a></li>
                    <li><a href="{{ route('procedural', 'kullanim-kosullari') }}" class="hover:text-accentTeal hover:translate-x-1 duration-200 transition-all inline-block py-1">Kullanım Koşulları</a></li>
                </ul>
            </div>

            <!-- ETBIS QR Area -->
            <div class="flex flex-col items-start">
                <h3 class="text-white text-sm font-extrabold mb-4 uppercase tracking-wider">ETBİS Kayıtlı Site</h3>
                <a href="https://www.eticaret.gov.tr" target="_blank" class="bg-white p-3 rounded-xl shadow-md border border-slate-700 hover:border-trendyol transition-all flex items-center justify-between gap-3" id="etbis-qr-container">
                    <div class="w-16 h-16 shrink-0 bg-slate-100 p-1 border border-slate-300 rounded flex flex-col justify-between">
                        <div class="flex justify-between">
                            <div class="w-3.5 h-3.5 bg-slate-900"></div>
                            <div class="w-3.5 h-3.5 bg-slate-900"></div>
                        </div>
                        <div class="w-full h-1 bg-slate-900 my-0.5"></div>
                        <div class="flex justify-between">
                            <div class="w-3.5 h-3.5 bg-slate-900"></div>
                            <div class="w-2.5 h-2.5 bg-slate-900"></div>
                        </div>
                    </div>
                    <div class="text-left text-slate-800">
                        <div class="font-extrabold text-[10px] uppercase text-trendyol tracking-wide">ETBİS</div>
                        <div class="text-[9px] leading-tight text-slate-550 font-semibold">Elektronik Ticaret Bilgi Sistemi</div>
                        <div class="text-[8px] bg-slate-100 border text-slate-600 font-bold px-1.5 py-0.5 mt-1 rounded uppercase tracking-wider">Kayıtlı Site</div>
                    </div>
                </a>
            </div>
            
        </div>
        
        <!-- Bottom Bar -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-slate-800 mt-12 pt-8 text-center text-xs text-slate-500 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex flex-col items-center sm:items-start gap-1">
                <p>&copy; 2026 Wise Solutions. Tüm Hakları Saklıdır.</p>
                <p class="text-[10px] text-slate-500 font-semibold">Wise Solutions Bilgi Teknolojileri Paz. ve Tic. Ltd. Şti. | info@wisesolutions.com.tr</p>
            </div>
            
            <!-- Safe Payment & SSL Badges -->
            <div class="flex flex-wrap justify-center items-center gap-4">
                <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700">
                    <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span class="text-[10px] text-slate-300 font-extrabold uppercase tracking-wider">iyzico Güvenli Ödeme</span>
                </div>
                
                <div class="flex items-center gap-1.5 bg-slate-800 px-3 py-1.5 rounded-lg border border-slate-700">
                    <svg class="h-4 w-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span class="text-[10px] text-slate-300 font-extrabold uppercase tracking-wider">256-Bit SSL</span>
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 bg-slate-800 text-slate-400 text-[10px] font-bold rounded border border-slate-700">VISA</span>
                    <span class="px-2 py-0.5 bg-slate-800 text-slate-400 text-[10px] font-bold rounded border border-slate-700">MASTERCARD</span>
                    <span class="px-2 py-0.5 bg-slate-800 text-slate-400 text-[10px] font-bold rounded border border-slate-700">TROY</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Slide-in Drawer -->
    <div x-show="mobileMenuOpen" class="relative z-50 md:hidden" role="dialog" aria-modal="true" style="display: none;">
        <!-- Background backdrop -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>

        <div class="fixed inset-0 flex z-50 justify-end">
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="relative max-w-xs w-full bg-white shadow-2xl flex flex-col py-4 pb-12 overflow-y-auto"
                 style="overscroll-behavior-y: contain;">
                
                <!-- Close Button -->
                <div class="px-4 flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-black text-trendyol">Wise Solutions</span>
                    </div>
                    <button type="button" @click="mobileMenuOpen = false" class="rounded-md p-2 text-slate-400 hover:text-slate-550 hover:bg-slate-100 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Links -->
                <div class="px-4 space-y-4">
                    <!-- Channel Selectors -->
                    <div class="space-y-2">
                        <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Mağaza Kanalları</div>
                        <a href="{{ route('electronics.home') }}" class="block px-3 py-2 rounded-lg text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-trendyol transition-colors">Elektronik</a>
                        <a href="{{ route('health.home') }}" class="block px-3 py-2 rounded-lg text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-trendyol transition-colors">Sağlık & Lens</a>
                        @if ($danismanlikAcik)
                        <a href="{{ route('consulting') }}" class="block px-3 py-2 rounded-lg text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-trendyol transition-colors">Danışmanlık & Dış Ticaret</a>
                        @endif
                    </div>

                    <hr class="border-slate-100">

                    <!-- Categories -->
                    <div class="space-y-1">
                        <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Kategoriler</div>
                        @foreach ($headerCategories as $cat)
                            <a href="{{ route('category', $cat->slug) }}" class="block px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-trendyol hover:bg-slate-50 rounded transition-colors">{{ $cat->name }}</a>
                        @endforeach
                    </div>

                    <hr class="border-slate-100">

                    <!-- User Account / Login -->
                    <div class="space-y-1">
                        @auth
                            <div class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Hesabım ({{ Auth::user()->name }})</div>
                            <a href="{{ route('profile.index') }}" class="block px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-trendyol">Profil Ayarları</a>
                            <a href="{{ route('profile.favorites') }}" class="block px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-trendyol">Favorilerim</a>
                            <a href="{{ route('profile.orders') }}" class="block px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-trendyol">Siparişlerim</a>
                            <form action="{{ route('logout') }}" method="POST" class="block w-full pt-1">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded">Çıkış Yap</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="block px-3 py-2 rounded-lg text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-trendyol transition-colors">Giriş Yap / Üye Ol</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cookie Consent Banner -->
    <div id="cookie-consent-banner" class="fixed bottom-0 inset-x-0 bg-slate-900 border-t border-slate-800 py-4 px-6 md:px-12 z-[9999] shadow-2xl flex flex-col md:flex-row items-center justify-between gap-4 transition-transform duration-500 translate-y-full">
        <div class="flex gap-3 items-start max-w-3xl">
            <svg class="h-6 w-6 text-trendyol shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
            </svg>
            <div class="text-xs sm:text-sm text-slate-300">
                <span class="font-bold text-white">Çerez Kullanımı Hakkında:</span> 
                Sitemizin temel işlevlerini sunabilmek, alışveriş deneyiminizi iyileştirmek ve trafik analizi gerçekleştirmek amacıyla çerezler kullanmaktayız. Detaylı bilgi için <a href="{{ route('procedural', 'cerez-politikasi') }}" class="text-trendyol hover:underline font-bold">Çerez Politikamızı</a> ve <a href="{{ route('kvkk') }}" class="text-trendyol hover:underline font-bold">KVKK Metnimizi</a> inceleyebilirsiniz.
            </div>
        </div>
        <div class="flex gap-3 shrink-0">
            <button onclick="setCookieConsent('rejected')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-bold transition-all">Reddet</button>
            <button onclick="setCookieConsent('accepted')" class="px-5 py-2 bg-trendyol hover:bg-trendyolDark text-white rounded-lg text-xs font-black shadow transition-all hover:scale-[1.02]">Kabul Et</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var consent = localStorage.getItem('cookie_consent');
            var banner = document.getElementById('cookie-consent-banner');
            if (!consent) {
                setTimeout(function() {
                    banner.classList.remove('translate-y-full');
                }, 1000);
            }
            if (consent === 'accepted') {
                runAnalyticalCookies();
            }
        });

        function setCookieConsent(status) {
            localStorage.setItem('cookie_consent', status);
            var banner = document.getElementById('cookie-consent-banner');
            banner.classList.add('translate-y-full');
            if (status === 'accepted') {
                runAnalyticalCookies();
            }
        }

        function notifyStock(productId) {
            @auth
                var userEmail = "{{ auth()->user()->email }}";
                Swal.fire({
                    title: 'Stok Bildirimi',
                    text: 'Bu ürün stoklarımıza girdiğinde ' + userEmail + ' adresine e-posta gönderilecektir.',
                    icon: 'success',
                    confirmButtonText: 'Tamam',
                    confirmButtonColor: '#005B96'
                });
            @else
                Swal.fire({
                    title: 'Stok Gelince Haber Ver',
                    input: 'email',
                    inputLabel: 'E-posta adresiniz',
                    inputPlaceholder: 'Örn: isim@domain.com',
                    showCancelButton: true,
                    confirmButtonText: 'Bildirim Oluştur',
                    cancelButtonText: 'İptal',
                    confirmButtonColor: '#005B96',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Lütfen geçerli bir e-posta adresi girin!'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Başarılı!',
                            text: 'E-posta adresiniz (' + result.value + ') başarıyla kaydedildi. Ürün stoklarımıza girdiğinde bilgilendirme gönderilecektir.',
                            icon: 'success',
                            confirmButtonText: 'Tamam',
                            confirmButtonColor: '#005B96'
                        });
                    }
                });
            @endauth
        }

        function runAnalyticalCookies() {
            console.log('Analitik çerezler etkinleştirildi.');
        }
    </script>

    <!-- Top-Right Cart Notification Component (AlpineJS) -->
    <div x-data="{ 
            show: false, 
            message: 'Sepete eklendi', 
            timer: null,
            showToast() {
                this.show = true;
                if (this.timer) clearTimeout(this.timer);
                this.timer = setTimeout(() => { this.show = false; }, 1500);
            }
        }"
        x-on:show-cart-toast.window="showToast()"
        x-show="show"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-x-8 opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="translate-x-8 opacity-0"
        class="fixed top-5 right-5 z-[99999] bg-white text-slate-800 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-slate-100 px-5 py-4 flex items-center gap-3.5 max-w-sm"
        style="display: none;"
    >
        <div class="bg-emerald-500 text-white rounded-full p-1.5 shrink-0 shadow-md shadow-emerald-100">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <div class="flex-grow">
            <p class="text-xs font-black text-slate-900">Sepete Eklendi</p>
            <p class="text-[10px] font-semibold text-slate-500 mt-0.5">Ürün sepetinize eklendi.</p>
        </div>
    </div>

    <!-- Toast Notification Component -->
    <div x-data="{ 
            show: false, 
            message: '', 
            type: 'success',
            init() {
                @if(session('success'))
                    this.showToast('{{ session('success') }}', 'success');
                @endif
                @if(session('error'))
                    this.showToast('{{ session('error') }}', 'error');
                @endif
                @if(session('info'))
                    this.showToast('{{ session('info') }}', 'info');
                @endif
            },
            showToast(msg, type = 'success') {
                this.message = msg;
                this.type = type;
                this.show = true;
                setTimeout(() => { this.show = false; }, 4000);
            }
        }"
        @show-toast.window="showToast($event.detail.message, $event.detail.type || 'success')"
        x-show="show"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-5 right-5 z-[9999] max-w-sm w-full bg-white rounded-2xl shadow-2xl border border-slate-100 p-4 flex items-start gap-3"
        style="display: none;"
    >
        <!-- Icon -->
        <template x-if="type === 'success'">
            <div class="bg-emerald-50 text-emerald-500 rounded-full p-1.5 shrink-0">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4" />
                </svg>
            </div>
        </template>
        <template x-if="type === 'error'">
            <div class="bg-rose-50 text-rose-500 rounded-full p-1.5 shrink-0">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </template>
        <template x-if="type === 'info'">
            <div class="bg-sky-50 text-sky-500 rounded-full p-1.5 shrink-0">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </template>

        <!-- Content -->
        <div class="flex-grow">
            <h4 class="text-xs font-black text-slate-800" x-text="{ success: 'Başarılı!', error: 'Hata!', info: 'Bilgi' }[type]"></h4>
            <p class="text-xs font-medium text-slate-500 mt-0.5" x-text="message"></p>
        </div>

        <!-- Close button -->
        <button @click="show = false" class="text-slate-400 hover:text-slate-655 focus:outline-none">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Mobile Sticky Bottom Navigation Bar -->
    <div class="fixed bottom-0 inset-x-0 bg-white/90 backdrop-blur-md border-t border-slate-200/80 z-[49] flex items-center justify-around py-2.5 md:hidden pb-safe-bottom shadow-lg">
        <!-- Home -->
        <a href="{{ route('landing') }}" class="flex flex-col items-center justify-center min-w-[56px] min-h-[48px] gap-0.5 text-slate-500 hover:text-trendyol transition-colors">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="text-[9px] font-bold uppercase tracking-wider">Ana Sayfa</span>
        </a>
        <!-- Categories -->
        <button @click="mobileMenuOpen = true" class="flex flex-col items-center justify-center min-w-[56px] min-h-[48px] gap-0.5 text-slate-500 hover:text-trendyol transition-colors focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <span class="text-[9px] font-bold uppercase tracking-wider">Kategoriler</span>
        </button>
        <!-- Cart -->
        <a href="{{ route('cart.index') }}" class="flex flex-col items-center justify-center min-w-[56px] min-h-[48px] gap-0.5 text-slate-500 hover:text-trendyol transition-colors relative">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            @php
                $cartItemCount = array_sum(array_column(session('cart', []), 'quantity'));
            @endphp
            @if($cartItemCount > 0)
                <span class="absolute top-0.5 right-0.5 bg-rose-500 text-white text-[9px] font-black w-4.5 h-4.5 rounded-full flex items-center justify-center border border-white leading-none shadow-sm">{{ $cartItemCount }}</span>
            @endif
            <span class="text-[9px] font-bold uppercase tracking-wider">Sepetim</span>
        </a>
        <!-- Account -->
        <a href="{{ route('profile.index') }}" class="flex flex-col items-center justify-center min-w-[56px] min-h-[48px] gap-0.5 text-slate-500 hover:text-trendyol transition-colors">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="text-[9px] font-bold uppercase tracking-wider">Profilim</span>
        </a>
    </div>

    @yield('modals')

    @yield('scripts')

    <script>
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form && (form.action.endsWith('/sepet/ekle') || form.action.includes('/sepet/ekle'))) {
                e.preventDefault();
                
                // Find the submit button inside this form
                const button = form.querySelector('button[type="submit"]');
                if (!button || button.disabled) return;
                
                // Disable the button to prevent spam
                button.disabled = true;
                
                // Store original content
                const originalHTML = button.innerHTML;
                
                // Change text and icon to "Sepete Eklendi" with a tick icon
                button.innerHTML = `
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Sepete Eklendi</span>
                `;
                
                // Submit the form via AJAX (fetch)
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Trigger top-right cart notification
                        window.dispatchEvent(new CustomEvent('show-cart-toast'));
                        // Update cart count badge
                        if (data.cartCount !== undefined) {
                            const desktopBadge = document.getElementById('cart-badge-count');
                            const mobileBadge = document.querySelector('a[href*="/sepet"] span.bg-rose-500');
                            
                            if (desktopBadge) {
                                desktopBadge.textContent = data.cartCount;
                            } else if (data.cartCount > 0) {
                                const cartLink = document.getElementById('header-cart-link');
                                if (cartLink) {
                                    const badge = document.createElement('span');
                                    badge.id = 'cart-badge-count';
                                    badge.className = 'absolute -top-1.5 -right-2 bg-trendyol text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center border border-white';
                                    badge.textContent = data.cartCount;
                                    cartLink.appendChild(badge);
                                }
                            }
                            
                            if (mobileBadge) {
                                mobileBadge.textContent = data.cartCount;
                            } else if (data.cartCount > 0) {
                                const mobileCartLink = document.querySelector('div.fixed.bottom-0 a[href*="/sepet"]');
                                if (mobileCartLink) {
                                    const badge = document.createElement('span');
                                    badge.className = 'absolute top-0.5 right-0.5 bg-rose-500 text-white text-[9px] font-black w-4.5 h-4.5 rounded-full flex items-center justify-center border border-white leading-none shadow-sm';
                                    badge.textContent = data.cartCount;
                                    mobileCartLink.appendChild(badge);
                                }
                            }
                        }
                    } else {
                        window.dispatchEvent(new CustomEvent('show-toast', { 
                            detail: { message: data.message || 'Bir hata oluştu.', type: 'error' } 
                        }));
                    }
                })
                .catch(error => {
                    console.error('Error adding to cart:', error);
                    window.dispatchEvent(new CustomEvent('show-toast', { 
                        detail: { message: 'Sepete eklenirken bir hata oluştu.', type: 'error' } 
                    }));
                })
                .finally(() => {
                    // Keep disabled for 1.5 seconds
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                    }, 1500);
                });
            }
        });
    </script>
</body>
</html>
