@extends('layouts.app')

@section('title', 'Sepetim - Wise Solutions')
@section('meta_description', 'Alışveriş sepetinizi görüntüleyin, sipariş miktarınızı ayarlayın ve güvenli bir şekilde alışverişinizi tamamlayın.')

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Anasayfa</a>
            <span>/</span>
            <span class="text-slate-700">Sepetim</span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mb-8" id="cart-title">Alışveriş Sepetim</h1>

        @if (count($cart) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start font-sans">
                
                <!-- Left: Cart Items List -->
                <div class="lg:col-span-8 space-y-4">
                    @foreach ($cart as $id => $item)
                        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 sm:p-5 flex items-center justify-between gap-4" id="cart-item-{{ $id }}">
                            
                            <!-- Item Image/SVG representation -->
                            <div class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-center p-2">
                                @if (Str::contains(Str::lower($item['name']), 'arduino'))
                                    <svg class="h-10 w-10 text-sky-700" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="10" y="20" width="80" height="60" rx="6" fill="#008184" />
                                        <rect x="5" y="30" width="15" height="12" rx="1" fill="#CCCCCC" />
                                        <rect x="35" y="42" width="40" height="14" rx="1" fill="#222222" />
                                    </svg>
                                @elseif (Str::contains(Str::lower($item['name']), 'raspberry'))
                                    <svg class="h-10 w-10 text-emerald-700" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="10" y="20" width="80" height="60" rx="8" fill="#C31C4A" />
                                        <rect x="38" y="38" width="22" height="22" rx="2" fill="#222222" />
                                    </svg>
                                @elseif (Str::contains(Str::lower($item['name']), 'esp32') || Str::contains(Str::lower($item['name']), 'd1 mini') || Str::contains(Str::lower($item['name']), 'beetle'))
                                    <svg class="h-10 w-10 text-slate-800" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="25" y="15" width="50" height="70" rx="4" fill="#1E293B" />
                                        <rect x="35" y="32" width="30" height="25" rx="1" fill="#94A3B8" />
                                    </svg>
                                @elseif (Str::contains(Str::lower($item['name']), 's tipi') || Str::contains(Str::lower($item['name']), 'cob') || Str::contains(Str::lower($item['name']), 'led'))
                                    <svg class="h-10 w-10 text-yellow-500" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="10" y="44" width="80" height="12" rx="3" fill="#D4AF37" />
                                        <rect x="12" y="46" width="76" height="8" rx="2" fill="#F59E0B" />
                                    </svg>
                                @elseif (Str::contains(Str::lower($item['name']), 'lens kabı') || Str::contains(Str::lower($item['name']), 'saklama kutusu'))
                                    <svg class="h-10 w-10 text-sky-500" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="15" y="35" width="70" height="30" rx="15" fill="#E2E8F0" stroke="#CBD5E1" stroke-width="1" />
                                        <circle cx="32" cy="50" r="9" fill="#3B82F6" />
                                        <circle cx="68" cy="50" r="9" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="1" />
                                    </svg>
                                @elseif (Str::contains(Str::lower($item['name']), 'dmv'))
                                    <svg class="h-10 w-10 text-rose-500" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="47" y="32" width="6" height="42" rx="3" fill="#EF4444" />
                                        <ellipse cx="50" cy="74" rx="10" ry="14" fill="#DC2626" />
                                    </svg>
                                @else
                                    <svg class="h-10 w-10 text-slate-600" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="20" y="30" width="60" height="40" rx="3" fill="#1E293B" />
                                    </svg>
                                @endif
                            </div>

                            <!-- Item Info -->
                            <div class="flex-grow min-w-0">
                                <a href="{{ route('product.detail', $item['slug']) }}" class="text-sm font-bold text-slate-800 hover:text-trendyol transition-colors block truncate" title="{{ $item['name'] }}">
                                    {{ $item['name'] }}
                                </a>
                                <div class="text-xs text-slate-400 font-bold mt-1">Adet Fiyatı: {{ number_format($item['price'], 2, ',', '.') }} TL <span class="text-[9px] text-slate-400 font-extrabold tracking-wider uppercase ml-0.5">(KDV Dahil)</span></div>

                                {{-- Adet Kontrolü --}}
                                <div class="flex items-center gap-2 mt-2">
                                    <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden">
                                        <button type="button"
                                            onclick="changeCartQty({{ $id }}, -1)"
                                            class="px-2.5 py-1.5 bg-slate-50 hover:bg-slate-100 font-bold text-slate-600 text-sm border-r border-slate-200 active:scale-95 transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                                            aria-label="Adeti azalt"
                                            @disabled($item['quantity'] <= 1)
                                            id="qty-minus-{{ $id }}">&minus;</button>
                                        <input type="number"
                                            id="qty-input-{{ $id }}"
                                            value="{{ $item['quantity'] }}"
                                            min="1"
                                            class="w-11 text-center text-xs font-bold text-slate-800 focus:outline-none py-1.5 bg-white"
                                            onchange="setCartQty({{ $id }}, this.value)"
                                            aria-label="Adet">
                                        <button type="button"
                                            onclick="changeCartQty({{ $id }}, 1)"
                                            class="px-2.5 py-1.5 bg-slate-50 hover:bg-slate-100 font-bold text-slate-600 text-sm border-l border-slate-200 active:scale-95 transition-all"
                                            aria-label="Adeti artır"
                                            id="qty-plus-{{ $id }}">+</button>
                                    </div>
                                    <span class="text-[10px] text-slate-400 font-bold" id="qty-status-{{ $id }}"></span>
                                </div>
                            </div>

                            <!-- Price and Delete -->
                            <div class="text-right shrink-0 flex flex-col items-end gap-2">
                                <span class="text-sm sm:text-base font-black text-slate-900">
                                    {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }} TL
                                    <span class="text-[9px] text-slate-400 font-bold block">(KDV Dahil)</span>
                                </span>
                                
                                <form action="{{ route('cart.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $id }}">
                                    <button type="submit" class="text-slate-400 hover:text-rose-500 transition-colors p-1" title="Sepetten Çıkar" id="btn-remove-{{ $id }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- Right: Summary & Order Info -->
                <div class="lg:col-span-4 space-y-6">
                    
                    <!-- Cost Box -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-4" id="cart-summary-box">
                        <h3 class="text-sm font-extrabold text-slate-900 border-b border-slate-100 pb-3">Sipariş Özeti</h3>
                        
                        <div class="space-y-2 text-xs font-semibold text-slate-500">
                            <div class="flex justify-between">
                                <span>Ara Toplam</span>
                                <span class="text-slate-800 font-extrabold">{{ number_format($total, 2, ',', '.') }} TL</span>
                            </div>
                            @if (isset($discount) && $discount > 0)
                                <div class="flex justify-between text-rose-600 font-bold">
                                    <span>İndirim</span>
                                    <span>-{{ number_format($discount, 2, ',', '.') }} TL</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span>Kargo Ücreti</span>
                                @if (($shippingCost ?? 0) > 0)
                                    <span class="text-slate-800 font-extrabold">{{ number_format($shippingCost, 2, ',', '.') }} TL</span>
                                @else
                                    <span class="text-emerald-600 font-extrabold">Kargo Bedava</span>
                                @endif
                            </div>

                            @if (!empty($freeShippingRemaining))
                                <div class="bg-amber-50 border border-amber-100 rounded-lg px-3 py-2 text-[11px] font-bold text-amber-700 leading-snug">
                                    Ücretsiz kargo için sepetinize
                                    <span class="font-black">{{ number_format($freeShippingRemaining, 2, ',', '.') }} TL</span>
                                    daha ekleyin.
                                </div>
                            @endif
                        </div>

                        {{-- Kupon Kodu Giriş Alanı --}}
                        <div class="border-t border-slate-100 pt-4 pb-2">
                            @if (isset($coupon) && $coupon)
                                <div class="flex items-center justify-between bg-emerald-50 border border-emerald-100 rounded-xl px-3.5 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800">Kupon Uygulandı</p>
                                            <p class="text-xs text-emerald-600 font-extrabold">{{ $coupon['code'] }} ({{ $coupon['type'] === 'percent' ? '%'.$coupon['value'] : $coupon['value'].' ₺' }} indirim)</p>
                                        </div>
                                    </div>
                                    <form action="{{ route('coupon.remove') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="text-slate-400 hover:text-rose-500 transition-colors p-1" title="Kuponu Kaldır">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="flex gap-2">
                                    <input type="text" id="coupon-code-input" placeholder="KUPON KODU" 
                                        class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all placeholder-slate-400 font-bold uppercase">
                                    <button type="button" onclick="submitCoupon()" class="bg-slate-800 hover:bg-slate-900 text-white font-extrabold px-4 py-2 rounded-lg text-xs transition duration-150 active:scale-95">
                                        Uygula
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-slate-100 pt-3 flex justify-between items-baseline">
                            <span class="text-sm font-bold text-slate-900">Genel Toplam</span>
                            <span class="text-lg font-black text-trendyol">{{ number_format(isset($netTotal) ? $netTotal : $total, 2, ',', '.') }} TL <span class="text-[10px] text-slate-400 font-extrabold tracking-wider uppercase ml-0.5">(KDV Dahil)</span></span>
                        </div>
                        
                        <!-- Checkout Actions -->
                        <div class="mt-4">
                            <a 
                                href="{{ route('checkout') }}" 
                                class="w-full bg-[#1B3A6B] hover:bg-[#142d54] text-white py-3 rounded-lg text-sm font-extrabold transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-1.5"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Ödeme Adımına Geç</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        @else
            <!-- Empty Cart state -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-12 text-center max-w-xl mx-auto my-8">
                <div class="bg-orange-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5 text-trendyol">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-slate-800 mb-2">Sepetinizde Ürün Bulunmuyor</h2>
                <p class="text-xs text-slate-400 font-semibold max-w-xs mx-auto mb-6">Mikroişlemci kartları ve sensörler arasından ihtiyacınız olanları sepetinize ekleyerek alışverişe başlayabilirsiniz.</p>
                <a href="{{ route('landing') }}" class="inline-block bg-trendyol hover:bg-trendyolDark text-white px-8 py-3 rounded-lg text-sm font-extrabold shadow-md hover:shadow-lg transition-all">Alışverişe Başla</a>
            </div>
        @endif

    </div>

    </div>

    <script>
        function submitCoupon() {
            const input = document.getElementById('coupon-code-input');
            if (!input || !input.value.trim()) return;

            const code = input.value.trim().toUpperCase();

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('coupon.apply') }}";

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = "{{ csrf_token() }}";
            form.appendChild(csrfToken);

            const codeInput = document.createElement('input');
            codeInput.type = 'hidden';
            codeInput.name = 'code';
            codeInput.value = code;
            form.appendChild(codeInput);

            document.body.appendChild(form);
            form.submit();
        }

        // --- Sepet adet güncelleme ---

        function changeCartQty(id, delta) {
            const input = document.getElementById('qty-input-' + id);
            if (!input) return;

            const next = parseInt(input.value, 10) + delta;
            if (next < 1) return;

            setCartQty(id, next);
        }

        function setCartQty(id, quantity) {
            quantity = parseInt(quantity, 10);

            const input  = document.getElementById('qty-input-' + id);
            const status = document.getElementById('qty-status-' + id);

            if (!Number.isInteger(quantity) || quantity < 1) {
                input.value = 1;
                quantity = 1;
            }

            setQtyButtonsDisabled(id, true);
            status.textContent = 'Güncelleniyor...';
            status.className = 'text-[10px] text-slate-400 font-bold';

            fetch("{{ route('cart.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                },
                body: JSON.stringify({ id: id, quantity: quantity }),
            })
            .then(async (response) => {
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    // Ürün artık satışta değil veya sepette yok — sayfayı tazele
                    status.textContent = data.message || 'Güncellenemedi';
                    status.className = 'text-[10px] text-rose-500 font-bold';
                    setTimeout(() => window.location.reload(), 1200);
                    return;
                }

                // Sunucu adeti stoğa göre kırpmış olabilir; otorite sunucudur.
                input.value = data.quantity;

                if (data.capped) {
                    status.textContent = data.message;
                    status.className = 'text-[10px] text-amber-600 font-bold';
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    // Ara toplam, kargo ve indirim yeniden hesaplansın
                    window.location.reload();
                }
            })
            .catch(() => {
                status.textContent = 'Bağlantı hatası';
                status.className = 'text-[10px] text-rose-500 font-bold';
                setQtyButtonsDisabled(id, false);
            });
        }

        function setQtyButtonsDisabled(id, disabled) {
            ['qty-minus-' + id, 'qty-plus-' + id, 'qty-input-' + id].forEach((elId) => {
                const el = document.getElementById(elId);
                if (el) el.disabled = disabled;
            });
        }
    </script>
@endsection
