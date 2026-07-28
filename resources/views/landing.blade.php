@php $danismanlikAcik = \App\Models\Setting::current()->consulting_enabled; @endphp
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="icon" type="image/jpeg" href="{{ asset('img/strong-modern-logo-for--wise-solutions---large-bol (1).jpg') }}">
    <title>Wise Solutions - Elektronik ve Lens Aksesuarları@if($danismanlikAcik), Dış Ticaret Danışmanlığı@endif</title>
    <meta name="description" content="ESP32 ve Arduino geliştirme kartları, orijinal DMV® lens aksesuarları@if($danismanlikAcik) ve dış ticaret danışmanlığı@endif. Kayseri merkezli Wise Solutions ile stoktan hızlı teslimat.">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Bu sayfa layouts.app'i KULLANMIYOR (bağımsız portal tasarımı), bu yüzden
         paylaşım etiketleri ve şema burada ayrıca tanımlanır. --}}
    <meta property="og:site_name" content="Wise Solutions">
    <meta property="og:locale" content="tr_TR">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Wise Solutions - Elektronik ve Lens Aksesuarları">
    <meta property="og:description" content="ESP32 ve Arduino geliştirme kartları ve orijinal DMV® lens aksesuarları.">
    <meta property="og:image" content="{{ asset('images/banner.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Wise Solutions">
    <meta name="twitter:description" content="Elektronik geliştirme kartları ve lens aksesuarları.">
    <meta name="twitter:image" content="{{ asset('images/banner.png') }}">

    @php
        $portalSemasi = [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => 'Wise Solutions',
            'url'      => url('/'),
            'publisher' => [
                '@type' => 'Organization',
                'name'  => 'Wise Solutions',
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => asset('img/strong-modern-logo-for--wise-solutions---large-bol (1).jpg'),
                ],
            ],
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => route('product.search') . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($portalSemasi, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        trendyol: '#1B4A7A', // Primary Navy Color
                        trendyolDark: '#143659',
                        darkNavy: '#0F172A',
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
            overflow-x: hidden;
        }
        /* Slanted Polygons for Desktop */
        @media (min-width: 768px) {
            .clip-section-1 {
                clip-path: polygon(0 0, 50% 0, 20% 100%, 0 100%);
            }
            .clip-section-2 {
                clip-path: polygon(50% 0, 80% 0, 50% 100%, 20% 100%);
            }
            .clip-section-3 {
                clip-path: polygon(80% 0, 100% 0, 100% 100%, 50% 100%);
            }
            
            /* Expand hover effect */
            .clip-section-1:hover {
                clip-path: polygon(0 0, 53% 0, 23% 100%, 0 100%);
                z-index: 30;
            }
            .clip-section-2:hover {
                clip-path: polygon(47% 0, 83% 0, 53% 100%, 17% 100%);
                z-index: 30;
            }
            .clip-section-3:hover {
                clip-path: polygon(77% 0, 100% 0, 100% 100%, 47% 100%);
                z-index: 30;
            }

            /* Danismanlik bolumu gizliyken iki bolme ekrani tam kaplar.
               Aksi halde ucuncu bolmenin yeri bos egik bir alan olarak kalirdi. */
            .iki-bolme .clip-section-1 {
                clip-path: polygon(0 0, 65% 0, 35% 100%, 0 100%);
            }
            .iki-bolme .clip-section-2 {
                clip-path: polygon(65% 0, 100% 0, 100% 100%, 35% 100%);
            }
            .iki-bolme .clip-section-1:hover {
                clip-path: polygon(0 0, 68% 0, 38% 100%, 0 100%);
                z-index: 30;
            }
            .iki-bolme .clip-section-2:hover {
                clip-path: polygon(62% 0, 100% 0, 100% 100%, 32% 100%);
                z-index: 30;
            }
        }
    </style>
</head>
<body class="h-screen bg-slate-950 flex flex-col md:relative md:overflow-hidden select-none">

    {{-- Sayfanın tek H1'i. Tasarım tam ekran görsel bölmelerden oluştuğu için
         görünür bir başlık yok; arama motorları ve ekran okuyucular için
         gizli ama okunabilir bir başlık + tanıtım metni bulunuyor.
         Bunlar `display:none` DEĞİL — sr-only, yani gizleme cezası almaz. --}}
    <h1 class="sr-only">Wise Solutions — Elektronik Geliştirme Kartları ve Lens Aksesuarları@if($danismanlikAcik), Dış Ticaret Danışmanlığı@endif</h1>
    <p class="sr-only">
        Kayseri merkezli Wise Solutions; ESP32, ESP8266 ve Arduino geliştirme kartları,
        sensör modülleri ve LED aydınlatma bileşenleri ile orijinal DMV® kontakt lens
        takma-çıkarma vantuzları ve lens saklama kutuları satmaktadır.@if($danismanlikAcik)
        Ayrıca dış ticaret ve ithalat danışmanlığı hizmeti vermektedir.@endif
        Aşağıdaki bölümlerden ilgilendiğiniz alanı seçebilirsiniz.
    </p>


    <!-- Mobile view: simple 3-row grid -->
    <div class="flex flex-col h-full md:hidden">
        
        <!-- Section 1: Elektronik -->
        <a href="{{ route('electronics.home') }}" class="flex-1 relative overflow-hidden group flex flex-col justify-center items-center text-center p-6 border-b border-white/10 active:scale-98 transition-all duration-200">
            <div class="absolute inset-0 bg-cover bg-center filter blur-[1.5px] brightness-[0.35] group-hover:scale-105 transition-transform duration-700" style="background-image: url('https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80');"></div>
            <div class="relative z-10 border-2 border-white/80 px-8 py-3.5 rounded-lg mb-3">
                <span class="text-white text-lg font-black uppercase tracking-wider">Elektronik</span>
            </div>
            <span class="relative z-10 text-trendyol font-extrabold text-xs tracking-widest uppercase bg-black/40 px-3 py-1 rounded">Alışverişe Başla</span>
        </a>

        <!-- Section 2: Genel Sağlık -->
        <a href="{{ route('health.home') }}" class="flex-1 relative overflow-hidden group flex flex-col justify-center items-center text-center p-6 border-b border-white/10 active:scale-98 transition-all duration-200">
            <div class="absolute inset-0 bg-cover bg-center filter blur-[1.5px] brightness-[0.35] group-hover:scale-105 transition-transform duration-700" style="background-image: url('/images/landing_health_bg.png');"></div>
            <div class="relative z-10 border-2 border-white/80 px-8 py-3.5 rounded-lg mb-3">
                <span class="text-white text-lg font-black uppercase tracking-wider">Genel Sağlık</span>
            </div>
            <span class="relative z-10 text-trendyol font-extrabold text-xs tracking-widest uppercase bg-black/40 px-3 py-1 rounded">Alışverişe Başla</span>
        </a>

        @if ($danismanlikAcik)
        <!-- Section 3: Danışmanlık ve Dış Ticaret -->
        <a href="{{ route('consulting') }}" class="flex-1 relative overflow-hidden group flex flex-col justify-center items-center text-center p-6 active:scale-98 transition-all duration-200">
            <div class="absolute inset-0 bg-cover bg-center filter blur-[1.5px] brightness-[0.35] group-hover:scale-105 transition-transform duration-700" style="background-image: url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=800&q=80');"></div>
            <div class="relative z-10 border-2 border-white/80 px-8 py-3.5 rounded-lg mb-3">
                <span class="text-white text-lg font-black uppercase tracking-wider">Danışmanlık ve Dış Ticaret</span>
            </div>
            <div class="relative z-10 flex flex-col gap-1.5 items-center">
                <span class="text-white text-[9px] font-bold tracking-widest uppercase bg-slate-900/60 px-2 py-0.5 rounded">Detaylı Bilgi İçin Tıklayın</span>
            </div>
        </a>
        @endif

    </div>

    <!-- Desktop view: 3-column slanted segments -->
    <div class="hidden md:block absolute inset-0 w-full h-full pointer-events-none @if(!$danismanlikAcik) iki-bolme @endif">
        
        <!-- Section 1: Elektronik -->
        <div class="absolute inset-0 w-full h-full z-10 pointer-events-none">
            <a href="{{ route('electronics.home') }}" class="w-full h-full block relative cursor-pointer pointer-events-auto clip-section-1 transition-all duration-500 group">
                <!-- Background Image -->
                <div class="absolute inset-0 bg-cover bg-center filter blur-[2px] group-hover:blur-0 brightness-[0.3] group-hover:brightness-[0.45] transition-all duration-700 scale-105 group-hover:scale-100" style="background-image: url('https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80');"></div>
                <!-- Slanted Boundary Highlight Line -->
                <div class="absolute top-0 right-0 h-full w-[2px] bg-gradient-to-b from-transparent via-white/20 group-hover:via-trendyol to-transparent transform -skew-x-[16.7deg] origin-top"></div>
                
                <!-- Content (Centered inside Section 1's column) -->
                <div class="absolute inset-y-0 left-0 w-[35%] flex flex-col justify-center items-center p-6 text-center select-none">
                    <div class="border-2 border-white/70 group-hover:border-trendyol px-6 py-4 rounded-lg mb-4 bg-slate-950/20 backdrop-blur-sm transition-all duration-300 transform group-hover:scale-105">
                        <h2 class="text-white text-xl lg:text-2xl font-black uppercase tracking-widest">Elektronik</h2>
                    </div>
                    <span class="text-white font-extrabold text-xs lg:text-sm tracking-wider uppercase bg-trendyol hover:bg-trendyolDark px-5 py-2.5 rounded-lg shadow-md transition-colors opacity-80 group-hover:opacity-100 whitespace-nowrap">Alışverişe Başla</span>
                </div>
            </a>
        </div>

        <!-- Section 2: Genel Sağlık -->
        <div class="absolute inset-0 w-full h-full z-10 pointer-events-none">
            <a href="{{ route('health.home') }}" class="w-full h-full block relative cursor-pointer pointer-events-auto clip-section-2 transition-all duration-500 group">
                <!-- Background Image -->
                <div class="absolute inset-0 bg-cover bg-center filter blur-[2px] group-hover:blur-0 brightness-[0.3] group-hover:brightness-[0.45] transition-all duration-700 scale-105 group-hover:scale-100" style="background-image: url('/images/landing_health_bg.png');"></div>
                <!-- Slanted Boundary Highlight Line -->
                <div class="absolute top-0 right-0 h-full w-[2px] bg-gradient-to-b from-transparent via-white/20 group-hover:via-trendyol to-transparent transform -skew-x-[16.7deg] origin-top"></div>
                
                <!-- Content (Centered inside Section 2's column) -->
                <div class="absolute inset-y-0 left-[30%] w-[40%] flex flex-col justify-center items-center p-6 text-center select-none">
                    <div class="border-2 border-white/70 group-hover:border-trendyol px-6 py-4 rounded-lg mb-4 bg-slate-950/20 backdrop-blur-sm transition-all duration-300 transform group-hover:scale-105">
                        <h2 class="text-white text-xl lg:text-2xl font-black uppercase tracking-widest">Genel Sağlık</h2>
                    </div>
                    <span class="text-white font-extrabold text-xs lg:text-sm tracking-wider uppercase bg-trendyol hover:bg-trendyolDark px-5 py-2.5 rounded-lg shadow-md transition-colors opacity-80 group-hover:opacity-100 whitespace-nowrap">Alışverişe Başla</span>
                </div>
            </a>
        </div>

        @if ($danismanlikAcik)
        <!-- Section 3: Danışmanlık ve Dış Ticaret -->
        <div class="absolute inset-0 w-full h-full z-10 pointer-events-none">
            <a href="{{ route('consulting') }}" class="w-full h-full block relative cursor-pointer pointer-events-auto clip-section-3 transition-all duration-500 group">
                <!-- Background Image -->
                <div class="absolute inset-0 bg-cover bg-center filter blur-[2px] group-hover:blur-0 brightness-[0.3] group-hover:brightness-[0.45] transition-all duration-700 scale-105 group-hover:scale-100" style="background-image: url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1200&q=80');"></div>
                
                <!-- Content (Centered inside Section 3's column) -->
                <div class="absolute inset-y-0 right-0 w-[35%] flex flex-col justify-center items-center p-6 text-center select-none">
                    <div class="border-2 border-white/70 group-hover:border-trendyol px-6 py-4 rounded-lg mb-4 bg-slate-950/20 backdrop-blur-sm transition-all duration-300 transform group-hover:scale-105">
                        <h2 class="text-white text-lg lg:text-xl font-black uppercase tracking-widest">Danışmanlık ve Dış Ticaret</h2>
                    </div>
                    <div class="flex flex-col gap-2 items-center">
                        <span class="text-slate-300 text-[10px] font-bold tracking-widest uppercase bg-slate-950/40 px-3 py-1 rounded">Detaylı Bilgi İçin Tıklayın</span>
                    </div>
                </div>
            </a>
        </div>
        @endif

    </div>

</body>
</html>
