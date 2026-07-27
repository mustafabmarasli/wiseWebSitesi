<div class="bg-white rounded-2xl shadow-sm border border-slate-100/80 overflow-hidden hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col group h-full relative" id="product-card-{{ $product->id }}">
    
    <!-- Discount, Popular, New & Coming Soon Badges -->
    <div class="absolute top-3 left-3 z-10 flex flex-col gap-1.5 items-start">
        @if ($product->stock <= 0)
            <span class="bg-slate-800/90 backdrop-blur-sm text-white text-[10px] sm:text-xs font-extrabold px-2.5 py-1 rounded-full shadow-sm flex items-center gap-1">
                ✈️ Yolda
            </span>
        @elseif ($product->eski_fiyat && $product->eski_fiyat > $product->price)
            @php 
                $discountPercentage = round((($product->eski_fiyat - $product->price) / $product->eski_fiyat) * 100);
            @endphp
            <span class="bg-rose-500 text-white text-[10px] sm:text-xs font-extrabold px-2.5 py-1 rounded-full shadow-sm animate-pulse flex items-center gap-1">
                🔥 %{{ $discountPercentage }} İndirim
            </span>
        @elseif ($product->created_at && $product->created_at->diffInDays(now()) < 7)
            <span class="bg-emerald-500 text-white text-[10px] sm:text-xs font-extrabold px-2.5 py-1 rounded-full shadow-sm flex items-center gap-1">
                🆕 Yeni
            </span>
        @elseif (isset($isPopular) && $isPopular || $product->satis_sayisi > 20)
            <span class="bg-amber-500 text-white text-[10px] sm:text-xs font-extrabold px-2.5 py-1 rounded-full shadow-sm flex items-center gap-1">
                ⭐ Çok Satan
            </span>
        @endif
    </div>

    <!-- Favorite Button -->
    <div class="absolute top-3 right-3 z-10">
        @auth
            @php
                $isFav = auth()->user()->favoriteProducts->contains($product->id);
            @endphp
            <form action="{{ route('favorite.toggle') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" class="bg-white/80 backdrop-blur-sm hover:bg-white text-rose-500 rounded-full p-2.5 shadow-sm border border-slate-100/50 transition-all hover:scale-110 flex items-center justify-center" title="Favorilere Ekle/Çıkar">
                    <svg class="h-4.5 w-4.5 {{ $isFav ? 'fill-current' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="bg-white/80 backdrop-blur-sm hover:bg-white text-slate-400 hover:text-rose-500 rounded-full p-2.5 shadow-sm border border-slate-100/50 transition-all hover:scale-110 flex items-center justify-center" title="Favorilere eklemek için giriş yapın">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </a>
        @endauth
    </div>

    <!-- Product Image (Dynamic SVG Illustration based on item type) -->
    <a href="{{ route('product.detail', $product->slug) }}" class="block bg-slate-50 border-b border-slate-100/60 p-4 flex items-center justify-center relative overflow-hidden h-44 sm:h-48 group-hover:bg-slate-100/30 transition-colors">
        
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" class="object-contain h-full w-full max-h-36 sm:max-h-40 group-hover:scale-105 transition-all duration-300">
        @else
            @if (Str::contains(Str::lower($product->name), 'beetle') || Str::contains(Str::lower($product->name), 'dfr1117'))
                <!-- SVG Beetle ESP32-C6 board -->
                <svg class="h-28 w-28 text-orange-600 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="25" y="30" width="50" height="40" rx="4" fill="#1b1b1b" stroke="#F27A1A" stroke-width="1.5" />
                    <rect x="35" y="38" width="30" height="24" rx="1" fill="#CCCCCC" />
                    <rect x="38" y="41" width="24" height="18" fill="#222222" />
                    <circle cx="44" cy="46" r="1" fill="#F27A1A" />
                    <rect x="22" y="34" width="3" height="32" fill="#D4AF37" />
                    <rect x="75" y="34" width="3" height="32" fill="#D4AF37" />
                    <circle cx="68" cy="62" r="1.5" fill="#E2E8F0" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'devkitc') || Str::contains(Str::lower($product->name), 'esp32-c6-devkit'))
                <!-- SVG ESP32-C6 DevKitC -->
                <svg class="h-28 w-28 text-slate-800 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="25" y="15" width="50" height="70" rx="3" fill="#1E293B" />
                    <rect x="30" y="24" width="40" height="28" rx="1" fill="#94A3B8" />
                    <rect x="33" y="27" width="34" height="22" fill="#334155" />
                    <rect x="32" y="81" width="12" height="5" fill="#CBD5E1" />
                    <rect x="56" y="81" width="12" height="5" fill="#CBD5E1" />
                    <circle cx="50" cy="62" r="2.5" fill="#FF0000" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 's tipi') || Str::contains(Str::lower($product->name), 'bükülebilir'))
                <!-- SVG S-Type LED Strip -->
                <svg class="h-28 w-28 text-yellow-500 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15 30c20 0 10 20 35 20s15 20 35 20" stroke="#E2E8F0" stroke-width="8" stroke-linecap="round" />
                    <path d="M15 30c20 0 10 20 35 20s15 20 35 20" stroke="#F59E0B" stroke-width="2" stroke-dasharray="1 6" stroke-linecap="round" />
                    <rect x="20" y="28" width="4" height="4" fill="#FFE57F" stroke="#F59E0B" />
                    <rect x="38" y="38" width="4" height="4" fill="#FFE57F" stroke="#F59E0B" />
                    <rect x="62" y="58" width="4" height="4" fill="#FFE57F" stroke="#F59E0B" />
                    <rect x="80" y="68" width="4" height="4" fill="#FFE57F" stroke="#F59E0B" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'cob'))
                <!-- SVG COB LED Strip -->
                <svg class="h-28 w-28 text-yellow-500 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="10" y="44" width="80" height="12" rx="3" fill="#D4AF37" />
                    <rect x="12" y="46" width="76" height="8" rx="2" fill="#F59E0B" class="animate-pulse" />
                    <line x1="12" y1="50" x2="88" y2="50" stroke="#FFFFFF" stroke-width="2" stroke-dasharray="2 3" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'esp32-cam'))
                <!-- SVG ESP32-CAM -->
                <svg class="h-28 w-28 text-slate-800 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="25" y="20" width="50" height="60" rx="3" fill="#1e293b" />
                    <circle cx="50" cy="40" r="10" fill="#222222" stroke="#475569" stroke-width="2" />
                    <circle cx="50" cy="40" r="4" fill="#000000" />
                    <rect x="44" y="34" width="12" height="12" stroke="#FFFFFF" stroke-width="0.8" />
                    <rect x="35" y="68" width="30" height="12" fill="#CBD5E1" />
                    <rect x="32" y="55" width="6" height="6" fill="#FFF" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'supermini') || Str::contains(Str::lower($product->name), 'super mini'))
                <!-- SVG SuperMini card -->
                <svg class="h-28 w-28 text-slate-800 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="32" y="20" width="36" height="60" rx="4" fill="#0F172A" stroke="#475569" />
                    <rect x="38" y="32" width="24" height="24" fill="#1E293B" />
                    <circle cx="50" cy="44" r="3" fill="#334155" />
                    <rect x="42" y="75" width="16" height="5" fill="#E2E8F0" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'd1 mini'))
                <!-- SVG D1 Mini -->
                <svg class="h-28 w-28 text-slate-800 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="28" y="25" width="44" height="50" rx="4" fill="#1E293B" />
                    <rect x="34" y="32" width="32" height="24" fill="#334155" />
                    <rect x="40" y="70" width="20" height="5" fill="#CBD5E1" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'devkit'))
                <!-- SVG DevKit 30 pin -->
                <svg class="h-28 w-28 text-slate-800 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="25" y="15" width="50" height="70" rx="3" fill="#1E293B" />
                    <rect x="35" y="25" width="30" height="25" fill="#334155" />
                    <rect x="40" y="77" width="20" height="8" fill="#CBD5E1" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'lens') || Str::contains(Str::lower($product->name), 'saklama kutusu') || Str::contains(Str::lower($product->name), 'aparat'))
                <!-- SVG Lens Case -->
                <svg class="h-28 w-28 text-sky-500 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="15" y="35" width="70" height="30" rx="15" fill="#E2E8F0" stroke="#CBD5E1" stroke-width="2" />
                    <circle cx="32" cy="50" r="11" fill="#3B82F6" />
                    <circle cx="32" cy="50" r="8" fill="#60A5FA" />
                    <text x="30" y="53" fill="#FFFFFF" font-size="9" font-family="sans-serif" font-weight="bold">L</text>
                    <circle cx="68" cy="50" r="11" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="1.5" />
                    <circle cx="68" cy="50" r="8" fill="#F8FAFC" />
                    <text x="65" y="53" fill="#64748B" font-size="9" font-family="sans-serif" font-weight="bold">R</text>
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'dmv'))
                <!-- SVG DMV Suction Tool -->
                <svg class="h-28 w-28 text-rose-500 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M35 30 C35 30, 50 15, 65 30 L62 33 C62 33, 50 24, 38 33 Z" fill="#EF4444" />
                    <rect x="47" y="32" width="6" height="42" rx="3" fill="#EF4444" />
                    <ellipse cx="50" cy="74" rx="10" ry="14" fill="#DC2626" />
                    <ellipse cx="47" cy="72" rx="3" ry="5" fill="#F87171" opacity="0.6" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'direnç') || Str::contains(Str::lower($product->name), 'ohm'))
                <!-- SVG Resistor -->
                <svg class="h-28 w-28 text-amber-800 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <line x1="10" y1="50" x2="90" y2="50" stroke="#94A3B8" stroke-width="3" stroke-linecap="round" />
                    <rect x="25" y="38" width="50" height="24" rx="12" fill="#FDE047" stroke="#CA8A04" stroke-width="2" />
                    <rect x="35" y="38" width="4" height="24" fill="#EF4444" />
                    <rect x="45" y="38" width="4" height="24" fill="#10B981" />
                    <rect x="55" y="38" width="4" height="24" fill="#3B82F6" />
                    <rect x="65" y="38" width="4" height="24" fill="#D97706" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'kondansatör') || Str::contains(Str::lower($product->name), 'kapasitör') || Str::contains(Str::lower($product->name), 'uf') || Str::contains(Str::lower($product->name), 'pf'))
                <!-- SVG Capacitor -->
                <svg class="h-28 w-28 text-sky-850 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <line x1="42" y1="50" x2="42" y2="90" stroke="#94A3B8" stroke-width="2.5" />
                    <line x1="58" y1="50" x2="58" y2="85" stroke="#94A3B8" stroke-width="2.5" />
                    <rect x="32" y="15" width="36" height="42" rx="4" fill="#0284C7" />
                    <rect x="32" y="15" width="8" height="42" fill="#E2E8F0" />
                    <text x="34" y="38" fill="#64748B" font-size="8" font-family="sans-serif" font-weight="bold">-</text>
                    <text x="43" y="38" fill="#FFFFFF" font-size="8" font-family="monospace">100uF</text>
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'havya'))
                <!-- SVG Soldering Iron -->
                <svg class="h-28 w-28 text-slate-700 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M72 28 L82 18 L78 14 L68 24 Z" fill="#94A3B8" />
                    <path d="M43 57 L70 30" stroke="#CBD5E1" stroke-width="6" stroke-linecap="square" />
                    <path d="M43 57 L70 30" stroke="#94A3B8" stroke-width="2" stroke-linecap="square" />
                    <path d="M18 82 L46 54" stroke="#3B82F6" stroke-width="12" stroke-linecap="round" />
                    <path d="M18 82 L46 54" stroke="#1D4ED8" stroke-width="4" stroke-linecap="round" />
                    <path d="M18 82 L10 90" stroke="#1E293B" stroke-width="3" stroke-linecap="round" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'lehim') || Str::contains(Str::lower($product->name), 'flux'))
                <!-- SVG Solder spool -->
                <svg class="h-28 w-28 text-slate-700 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="50" cy="50" rx="30" ry="12" fill="#1E293B" />
                    <rect x="23" y="38" width="54" height="24" fill="#94A3B8" stroke="#CBD5E1" stroke-width="1" />
                    <line x1="23" y1="44" x2="77" y2="44" stroke="#475569" stroke-width="1.5" />
                    <line x1="23" y1="50" x2="77" y2="50" stroke="#475569" stroke-width="1.5" />
                    <line x1="23" y1="56" x2="77" y2="56" stroke="#475569" stroke-width="1.5" />
                    <ellipse cx="50" cy="38" rx="30" ry="12" fill="#334155" />
                    <ellipse cx="50" cy="38" rx="10" ry="4" fill="#000000" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'kutu') || Str::contains(Str::lower($product->name), 'muhafaza'))
                <!-- SVG Box -->
                <svg class="h-28 w-28 text-slate-600 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M50 20 L85 35 L50 50 L15 35 Z" fill="#64748B" />
                    <path d="M15 35 L50 50 L50 80 L15 65 Z" fill="#475569" />
                    <path d="M50 50 L85 35 L85 65 L50 80 Z" fill="#334155" />
                    <circle cx="50" cy="24" r="1.5" fill="#94A3B8" />
                    <circle cx="81" cy="35" r="1.5" fill="#94A3B8" />
                    <circle cx="50" cy="46" r="1.5" fill="#94A3B8" />
                    <circle cx="19" cy="35" r="1.5" fill="#94A3B8" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'set') || Str::contains(Str::lower($product->name), 'kit'))
                <!-- SVG Kit Box -->
                <svg class="h-28 w-28 text-emerald-600 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="15" y="20" width="70" height="60" rx="6" fill="#00979D" stroke="#008184" stroke-width="2" />
                    <rect x="25" y="32" width="50" height="36" rx="2" fill="#FFFFFF" />
                    <text x="32" y="47" fill="#00979D" font-size="8" font-family="sans-serif" font-weight="extrabold">ARDUINO</text>
                    <text x="36" y="58" fill="#475569" font-size="7" font-family="sans-serif" font-weight="bold">PROJE SETI</text>
                    <circle cx="44" cy="42" r="2" stroke="#00979D" stroke-width="1" />
                    <circle cx="56" cy="42" r="2" stroke="#00979D" stroke-width="1" />
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'smd'))
                <!-- SVG SMD Chip -->
                <svg class="h-28 w-28 text-slate-800 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="30" y="35" width="40" height="30" rx="2" fill="#1b1b1b" stroke="#475569" stroke-width="1.5" />
                    <rect x="26" y="40" width="4" height="6" fill="#CBD5E1" />
                    <rect x="26" y="54" width="4" height="6" fill="#CBD5E1" />
                    <rect x="70" y="40" width="4" height="6" fill="#CBD5E1" />
                    <rect x="70" y="54" width="4" height="6" fill="#CBD5E1" />
                    <text x="38" y="53" fill="#64748B" font-size="8" font-family="monospace">SMD</text>
                </svg>
            @elseif (Str::contains(Str::lower($product->name), 'atmega') || Str::contains(Str::lower($product->name), 'pic1') || Str::contains(Str::lower($product->name), 'attiny') || Str::contains(Str::lower($product->name), 'entegre') || Str::contains(Str::lower($product->name), 'mcu'))
                <!-- SVG IC/MCU -->
                <svg class="h-28 w-28 text-slate-900 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="22" y="30" width="56" height="40" rx="3" fill="#1E293B" stroke="#0F172A" stroke-width="1.5" />
                    <path d="M22 45 C25 45, 25 55, 22 55" fill="#0F172A" />
                    <rect x="28" y="22" width="4" height="8" fill="#CBD5E1" />
                    <rect x="36" y="22" width="4" height="8" fill="#CBD5E1" />
                    <rect x="44" y="22" width="4" height="8" fill="#CBD5E1" />
                    <rect x="52" y="22" width="4" height="8" fill="#CBD5E1" />
                    <rect x="60" y="22" width="4" height="8" fill="#CBD5E1" />
                    <rect x="68" y="22" width="4" height="8" fill="#CBD5E1" />
                    <rect x="28" y="70" width="4" height="8" fill="#CBD5E1" />
                    <rect x="36" y="70" width="4" height="8" fill="#CBD5E1" />
                    <rect x="44" y="70" width="4" height="8" fill="#CBD5E1" />
                    <rect x="52" y="70" width="4" height="8" fill="#CBD5E1" />
                    <rect x="60" y="70" width="4" height="8" fill="#CBD5E1" />
                    <rect x="68" y="70" width="4" height="8" fill="#CBD5E1" />
                    <text x="32" y="54" fill="#94A3B8" font-size="10" font-family="monospace" font-weight="bold">MCU</text>
                </svg>
            @else
                <!-- Default chip SVG -->
                <svg class="h-28 w-28 text-slate-700 group-hover:scale-105 transition-all duration-300" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="20" y="30" width="60" height="40" rx="3" fill="#1E293B" />
                    <text x="32" y="54" fill="#94A3B8" font-size="10" font-family="monospace" font-weight="bold">CHIP</text>
                    <rect x="28" y="24" width="4" height="6" fill="#CBD5E1" />
                    <rect x="38" y="24" width="4" height="6" fill="#CBD5E1" />
                    <rect x="48" y="24" width="4" height="6" fill="#CBD5E1" />
                    <rect x="28" y="70" width="4" height="6" fill="#CBD5E1" />
                    <rect x="38" y="70" width="4" height="6" fill="#CBD5E1" />
                    <rect x="48" y="70" width="4" height="6" fill="#CBD5E1" />
                </svg>
            @endif
        @endif

    </a>

    <!-- Product Details -->
    <div class="p-4 flex flex-col flex-grow">
        
        <!-- Category & Stars -->
        <div class="flex items-center justify-between text-[11px] sm:text-xs font-extrabold uppercase tracking-wider text-slate-450 mb-1.5">
            <span>{{ $product->category->name }}</span>
            @if ($product->rating > 0)
            <span class="flex items-center text-amber-500 gap-0.5">
                <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                <span class="text-slate-700 font-extrabold text-xs">{{ number_format($product->rating, 1) }}</span>
            </span>
            @endif
        </div>

        <!-- Title -->
        <a href="{{ route('product.detail', $product->slug) }}" class="text-sm font-bold text-slate-800 group-hover:text-trendyol line-clamp-2 transition-colors mb-2.5 flex-grow" title="{{ $product->name }}">
            {{ $product->name }}
        </a>

        <!-- Stock Status -->
        <div class="mb-2 text-[11px] sm:text-xs font-bold">
            @if ($product->stock <= 0)
                <span class="text-indigo-600 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span> ✈️ Ürün Yolda, Gelmek Üzere
                </span>
            @elseif ($product->stock > 5)
                <span class="text-emerald-600 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Stokta Var
                </span>
            @else
                <span class="text-rose-600 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span> Son {{ $product->stock }} Ürün!
                </span>
            @endif
        </div>

        <!-- Installment Info -->
        <div class="mb-3 text-[11px] sm:text-xs font-semibold text-slate-400 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-slate-350" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span>Vade farksız 3 x {{ number_format($product->price / 3, 2, ',', '.') }} TL taksit</span>
        </div>

        <!-- Price Area -->
        <div class="flex flex-col mb-4">
            @if ($product->eski_fiyat && $product->eski_fiyat > $product->price)
                <div class="flex items-baseline gap-1.5 flex-wrap">
                    <span class="text-xs text-slate-450 line-through font-semibold">{{ number_format($product->eski_fiyat, 2, ',', '.') }} TL</span>
                    <span class="text-base font-black text-rose-600">{{ number_format($product->price, 2, ',', '.') }} TL</span>
                </div>
            @else
                <span class="text-base font-black text-slate-900">{{ number_format($product->price, 2, ',', '.') }} TL</span>
            @endif
        </div>

        <!-- Add to Cart / Coming Soon -->
        @if ($product->stock <= 0)
            <button type="button" onclick="notifyStock({{ $product->id }})" class="mt-auto w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 py-2.5 rounded-xl text-xs font-extrabold flex items-center justify-center gap-1.5 transition-all active:scale-95">
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span>Haber Ver</span>
            </button>
        @else
            <form action="{{ route('cart.add') }}" method="POST" class="mt-auto">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button 
                    type="submit" 
                    class="w-full bg-trendyol hover:bg-trendyolDark text-white py-2.5 rounded-xl text-xs font-extrabold transition-all duration-200 flex items-center justify-center gap-1.5 shadow-sm hover:shadow hover:scale-[1.01] active:scale-95"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Sepete Ekle</span>
                </button>
            </form>
        @endif

    </div>
</div>
