@extends('layouts.app')

@section('title', 'Güvenli Ödeme - Buy WISEly')
@section('meta_description', 'Siparişinizi tamamlayın. 256-bit SSL şifreleme ve iyzico güvencesiyle güvenli ödeme.')

@section('content')

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 font-sans">

    {{-- Progress Steps --}}
    <div class="mb-10 max-w-xl mx-auto">
        <div class="flex items-center justify-between text-xs sm:text-sm font-bold text-slate-400">
            <div class="flex items-center gap-1.5 text-slate-650 font-extrabold">
                <div class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-black text-slate-650">✓</div>
                <span>Sepetim</span>
            </div>
            <div class="w-8 sm:w-16 h-0.5 bg-slate-200"></div>
            <div class="flex items-center gap-1.5 text-trendyol font-extrabold">
                <div class="w-5 h-5 rounded-full bg-trendyol text-white flex items-center justify-center text-[10px] font-black">2</div>
                <span>Adres & Bilgiler</span>
            </div>
            <div class="w-8 sm:w-16 h-0.5 bg-slate-200"></div>
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-400">3</div>
                <span>Ödeme</span>
            </div>
            <div class="w-8 sm:w-16 h-0.5 bg-slate-200"></div>
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-400">4</div>
                <span>Sipariş Onayı</span>
            </div>
        </div>
    </div>

    {{-- Başlık --}}
    <div class="mb-8 text-center">
        <div class="inline-flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2 rounded-full text-sm font-bold mb-4">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            256-bit SSL Güvenli Ödeme
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900">Siparişi Tamamla</h1>
        <p class="text-slate-500 text-sm mt-1">
            @if ($setting->offersCardPayment())
                Kart bilgileriniz iyzico altyapısıyla şifrelenerek işlenir. Sitemize ulaşmaz.
            @else
                Bilgileriniz 256-bit SSL ile şifrelenerek iletilir.
            @endif
        </p>
    </div>

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl p-4 mb-6">
            <ul class="list-disc list-inside text-sm space-y-1 font-semibold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl p-4 mb-6 font-semibold text-sm">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('payment.initiate') }}" method="POST" id="checkout-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

            {{-- SOL: Müşteri Bilgileri --}}
            <div class="lg:col-span-3 space-y-6">

                {{-- Kayıtlı Adresler --}}
                @if (!empty($savedAddresses) && count($savedAddresses) > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6" id="kayitli-adresler">
                        <h2 class="text-base font-extrabold text-slate-800 mb-1.5 flex items-center gap-2">
                            <svg class="w-5 h-5 text-trendyol" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Kayıtlı Adreslerim
                        </h2>
                        <p class="text-xs text-slate-500 font-semibold mb-4">Bir adres seçin, form otomatik dolsun.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($savedAddresses as $adres)
                                <button type="button"
                                        class="kayitli-adres text-left bg-slate-50 hover:bg-white border border-slate-200 hover:border-trendyol rounded-xl px-4 py-3 transition-all active:scale-[0.99]"
                                        data-adres='@json($adres)'>
                                    <span class="block text-xs font-black text-slate-800">{{ $adres['title'] }}</span>
                                    <span class="block text-[11px] text-slate-500 font-semibold mt-0.5 leading-snug">
                                        {{ $adres['first_name'] }} {{ $adres['last_name'] }} —
                                        {{ $adres['neighborhood_name'] ?? '' }}
                                        {{ $adres['district_name'] ?? '' }}
                                        {{ $adres['province_name'] ?? $adres['city'] ?? '' }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Kişisel Bilgiler --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-base font-extrabold text-slate-800 mb-5 flex items-center gap-2">
                        <div class="bg-trendyol text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-black">1</div>
                        Kişisel Bilgiler
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Ad *</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200"
                                placeholder="Adınız" required>
                        </div>
                        <div>
                            <label for="last_name" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Soyad *</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200"
                                placeholder="Soyadınız" required>
                        </div>
                        <div>
                            <label for="email" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">E-posta *</label>
                            <input type="email" id="email" name="email" value="{{ old('email', auth()->user()?->email) }}"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200"
                                placeholder="ornek@mail.com" required>
                        </div>
                        <div>
                            <label for="phone" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Telefon *</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" inputmode="tel" maxlength="17"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200"
                                placeholder="+90 545 545 54 45" required>
                        </div>
                        {{-- TC Kimlik No koşullu: ticari faturada, fatura düzenleme
                             haddini aşan tutarlarda ve kartla ödemede zorunlu.
                             Zorunluluk sunucuda da yeniden denetleniyor. --}}
                        <div>
                            <label for="identity_number" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">
                                TC Kimlik No <span id="tc-yildiz" class="{{ $tcZorunlu ? '' : 'hidden' }}">*</span>
                            </label>
                            <input type="text" id="identity_number" name="identity_number" value="{{ old('identity_number') }}"
                                maxlength="11" inputmode="numeric"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200"
                                placeholder="11 haneli TC Kimlik No" @required($tcZorunlu)>
                            <p id="tc-aciklama" class="text-[11px] text-slate-500 font-semibold mt-1.5 {{ $tcZorunlu ? 'hidden' : '' }}">
                                Bu tutarda fatura için gerekmiyor, boş bırakabilirsiniz.
                            </p>
                            <p id="tc-aciklama-zorunlu" class="text-[11px] text-slate-500 font-semibold mt-1.5 {{ $tcZorunlu ? '' : 'hidden' }}">
                                Fatura mevzuatı gereği bu sipariş için zorunludur.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Teslimat Adresi --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-base font-extrabold text-slate-800 mb-5 flex items-center gap-2">
                        <div class="bg-trendyol text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-black">2</div>
                        Teslimat Adresi
                    </h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="province_id" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">İl *</label>
                                <select id="province_id" name="province_id" required
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200">
                                    <option value="">Seçiniz</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="district_id" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">İlçe *</label>
                                <select id="district_id" name="district_id" required disabled
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200">
                                    <option value="">İl Seçiniz</option>
                                </select>
                            </div>
                            <div>
                                <label for="neighborhood_id" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Mahalle *</label>
                                <select id="neighborhood_id" name="neighborhood_id" required disabled
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200">
                                    <option value="">İlçe Seçiniz</option>
                                </select>
                            </div>
                            <div>
                                <label for="zip_code" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Posta Kodu</label>
                                <input type="text" id="zip_code" name="zip_code" value="{{ old('zip_code') }}" inputmode="numeric" pattern="[0-9]*"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200"
                                    placeholder="34000">
                            </div>
                        </div>
                        <div>
                            <label for="address_detail" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Adres Detayı (Sokak, Bina, Daire vb.) *</label>
                            <textarea id="address_detail" name="address_detail" rows="3" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200 resize-none"
                                placeholder="Cadde, sokak, bina no, daire no...">{{ old('address_detail') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Fatura Bilgileri --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6" id="billing-section">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                            <div class="bg-trendyol text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-black">3</div>
                            Fatura Bilgileri
                        </h2>
                        {{-- Fatura = Teslimat Adresi Toggle --}}
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <span class="text-xs font-bold text-slate-500">Teslimat adresiyle aynı</span>
                            <div class="relative">
                                <input type="checkbox" id="billing-same-toggle" name="billing_same" value="1" class="sr-only peer" checked onchange="toggleBillingSection()">
                                <div class="w-10 h-5 bg-slate-200 peer-checked:bg-trendyol rounded-full transition-colors duration-200"></div>
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                            </div>
                        </label>
                    </div>

                    {{-- Farklı fatura adresi alanları (toggle off olunca görünür) --}}
                    <div id="billing-address-fields" class="hidden space-y-4 mb-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="billing_province_id" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Fatura İl *</label>
                                <select id="billing_province_id" name="billing_province_id"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200">
                                    <option value="">Seçiniz</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('billing_province_id') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="billing_district_id" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Fatura İlçe *</label>
                                <select id="billing_district_id" name="billing_district_id" disabled
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200">
                                    <option value="">İl Seçiniz</option>
                                </select>
                            </div>
                            <div>
                                <label for="billing_neighborhood_id" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Fatura Mahalle *</label>
                                <select id="billing_neighborhood_id" name="billing_neighborhood_id" disabled
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200">
                                    <option value="">İlçe Seçiniz</option>
                                </select>
                            </div>
                            <div>
                                <label for="billing_zip" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Fatura Posta Kodu</label>
                                <input type="text" id="billing_zip" name="billing_zip" value="{{ old('billing_zip') }}" inputmode="numeric" pattern="[0-9]*"
                                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200"
                                    placeholder="34000">
                            </div>
                        </div>
                        <div>
                            <label for="billing_address_detail" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Fatura Adres Detayı *</label>
                            <textarea id="billing_address_detail" name="billing_address_detail" rows="3"
                                class="w-full bg-slate-50 border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-white focus:border-transparent transition-all duration-200 resize-none"
                                placeholder="Cadde, sokak, bina no, daire no...">{{ old('billing_address_detail') }}</textarea>
                        </div>
                    </div>

                    {{-- Ticari Fatura Toggle --}}
                    <div class="border-t border-slate-100 pt-4">
                        <label class="flex items-center gap-3 cursor-pointer select-none group">
                            <div class="relative">
                                <input type="checkbox" id="corporate-invoice-toggle" name="is_corporate" value="1"
                                    class="sr-only peer" {{ old('is_corporate') ? 'checked' : '' }}
                                    onchange="toggleCorporateFields()">
                                <div class="w-10 h-5 bg-slate-200 peer-checked:bg-amber-500 rounded-full transition-colors duration-200"></div>
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                            </div>
                            <div>
                                <span class="text-sm font-extrabold text-slate-700 group-hover:text-slate-900 transition-colors">Ticari fatura istiyorum</span>
                                <p class="text-[11px] text-slate-400 font-medium">Şirket adına fatura almak istiyorsanız aktif edin</p>
                            </div>
                        </label>

                        {{-- Ticari fatura alanları --}}
                        <div id="corporate-fields" class="{{ old('is_corporate') ? '' : 'hidden' }} mt-4 space-y-4 bg-amber-50/60 border border-amber-100 rounded-xl p-4">
                            <div class="flex items-start gap-2 text-amber-700 bg-amber-100 rounded-lg px-3 py-2 mb-3">
                                <svg class="h-4 w-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-xs font-bold">Girilen bilgilerin doğruluğundan tarafım sorumludur. Hatalı bilgi nedeniyle oluşabilecek e-fatura sorunlarından Buy WISEly sorumlu tutulamaz.</p>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label for="company_name" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Şirket / Ticaret Unvanı *</label>
                                    <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}"
                                        class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white focus:border-transparent transition-all duration-200"
                                        placeholder="ABC Ticaret A.Ş.">
                                </div>
                                <div>
                                    <label for="tax_number" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Vergi Numarası *</label>
                                    <input type="text" id="tax_number" name="tax_number" value="{{ old('tax_number') }}"
                                        maxlength="10" inputmode="numeric"
                                        class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white focus:border-transparent transition-all duration-200"
                                        placeholder="10 haneli vergi no">
                                </div>
                                <div>
                                    <label for="tax_office" class="block text-xs font-extrabold text-slate-600 uppercase tracking-wider mb-1.5">Vergi Dairesi *</label>
                                    <input type="text" id="tax_office" name="tax_office" value="{{ old('tax_office') }}"
                                        class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:bg-white focus:border-transparent transition-all duration-200"
                                        placeholder="Kadıköy V.D.">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- ÖDEME YÖNTEMİ --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-base font-extrabold text-slate-800 mb-5 flex items-center gap-2">
                        <div class="bg-trendyol text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-black">4</div>
                        Ödeme Yöntemi
                    </h2>

                    <div class="space-y-3">
                        @if ($setting->offersBankTransfer())
                            <label class="odeme-secenegi flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all">
                                <input type="radio" name="payment_type" value="bank_transfer" required
                                       @checked(old('payment_type', $defaultPaymentType) === 'bank_transfer')
                                       onchange="odemeYontemiDegisti()"
                                       class="mt-0.5 w-4 h-4 text-trendyol focus:ring-trendyol accent-trendyol shrink-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-extrabold text-slate-800">Havale / EFT</span>
                                        @if ((float) $setting->bank_transfer_discount_percent > 0)
                                            <span class="text-[10px] font-black text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full uppercase tracking-wide">
                                                %{{ rtrim(rtrim(number_format((float) $setting->bank_transfer_discount_percent, 2, ',', '.'), '0'), ',') }} İndirim
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 font-medium mt-1 leading-relaxed">
                                        Siparişinizi tamamladıktan sonraki adımda banka bilgileri gösterilecektir.
                                    </p>
                                </div>
                            </label>
                        @endif

                        @if ($setting->offersCardPayment())
                            <label class="odeme-secenegi flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all">
                                <input type="radio" name="payment_type" value="card" required
                                       @checked(old('payment_type', $defaultPaymentType) === 'card')
                                       onchange="odemeYontemiDegisti()"
                                       class="mt-0.5 w-4 h-4 text-trendyol focus:ring-trendyol accent-trendyol shrink-0">
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm font-extrabold text-slate-800">Kredi / Banka Kartı</span>
                                    <p class="text-xs text-slate-500 font-medium mt-1 leading-relaxed">
                                        Kart bilgileriniz hiçbir zaman sitemizde saklanmaz. Tüm işlemler iyzico'nun PCI-DSS sertifikalı altyapısıyla gerçekleşir.
                                    </p>
                                </div>
                            </label>
                        @else
                            {{-- Kart altyapısı henüz açılmadı; seçenek gizlenmek yerine
                                 pasif gösteriliyor ki müşteri yakında geleceğini bilsin. --}}
                            <div class="flex items-start gap-3 p-4 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/60 cursor-not-allowed">
                                <div class="mt-0.5 w-4 h-4 rounded-full border-2 border-slate-300 shrink-0"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-extrabold text-slate-400">Kredi / Banka Kartı</span>
                                        <span class="text-[10px] font-black text-slate-500 bg-slate-200 px-2 py-0.5 rounded-full uppercase tracking-wide">Çok Yakında</span>
                                    </div>
                                    <p class="text-xs text-slate-400 font-medium mt-1">Kart ile ödeme seçeneği çok yakında hizmetinizde olacaktır.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    @error('payment_type')
                        <p class="text-rose-500 text-xs mt-3 font-bold">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- SAĞ: Sipariş Özeti --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-6">
                    <h2 class="text-base font-extrabold text-slate-800 mb-5 flex items-center gap-2">
                        <div class="bg-trendyol text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-black">5</div>
                        Sipariş Özeti
                    </h2>

                    <div class="space-y-3 mb-5">
                        @foreach ($cart as $id => $details)
                            <div class="flex items-start gap-3">
                                @if (!empty($details['image_url']))
                                    <img src="{{ $details['image_url'] }}" alt="{{ $details['name'] }}" loading="lazy"
                                         class="w-12 h-12 object-cover rounded-lg border border-slate-100 shrink-0">
                                @else
                                    <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center shrink-0">
                                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-800 line-clamp-2">{{ $details['name'] }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $details['quantity'] }} adet</p>
                                </div>
                                <span class="text-sm font-black text-slate-900 shrink-0">
                                    {{ number_format($details['price'] * $details['quantity'], 2, ',', '.') }} ₺
                                </span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Kupon Kodu --}}
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
                                <button type="button" onclick="submitRemoveCoupon()" class="text-slate-400 hover:text-rose-500 transition-colors p-1" title="Kuponu Kaldır">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
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

                    <div class="border-t border-slate-100 pt-4 space-y-2">
                        <div class="flex justify-between text-sm text-slate-600 font-semibold">
                            <span>Ara Toplam</span>
                            <span>{{ number_format($total, 2, ',', '.') }} ₺</span>
                        </div>
                        @if (isset($discount) && $discount > 0)
                            <div class="flex justify-between text-sm text-rose-600 font-bold">
                                <span>İndirim</span>
                                <span>-{{ number_format($discount, 2, ',', '.') }} ₺</span>
                            </div>
                        @endif
                        {{-- Havale indirimi ödeme yöntemine bağlı; JS ile açılıp kapanır. --}}
                        <div id="havale-indirim-satiri"
                             class="flex justify-between text-sm text-emerald-600 font-bold {{ ($bankDiscount ?? 0) > 0 ? '' : 'hidden' }}">
                            <span>Havale / EFT İndirimi</span>
                            <span id="havale-indirim-tutari">-{{ number_format($bankDiscount ?? 0, 2, ',', '.') }} ₺</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-650 font-semibold">
                            <span>Kargo</span>
                            @if (($shippingCost ?? 0) > 0)
                                <span class="text-slate-800 font-extrabold">{{ number_format($shippingCost, 2, ',', '.') }} ₺</span>
                            @else
                                <span class="text-emerald-600 font-extrabold">Ücretsiz</span>
                            @endif
                        </div>
                        <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-100">
                            <span>Toplam</span>
                            <span class="text-trendyol" id="genel-toplam">{{ number_format($netTotal, 2, ',', '.') }} ₺</span>
                        </div>
                        <p class="text-[10px] text-slate-400 font-semibold text-right">KDV Dahil</p>
                    </div>

                    {{-- Onay Kutuları --}}
                    <div class="border-t border-slate-100 pt-4 space-y-3">
                        <p class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Sözleşmeler & Onay</p>

                        <label class="flex items-start gap-2.5 cursor-pointer group">
                            <input type="checkbox" id="agree_sales" name="agree_sales" value="1" required
                                class="mt-0.5 w-4 h-4 rounded border-slate-300 text-trendyol focus:ring-trendyol accent-trendyol shrink-0">
                            <span class="text-xs text-slate-600 font-semibold leading-relaxed">
                                <a href="{{ route('procedural', 'mesafeli-satis') }}" class="font-extrabold text-trendyol underline hover:text-trendyolDark" target="_blank">Mesafeli Satış Sözleşmesi</a>'ni okudum ve kabul ediyorum. *
                            </span>
                        </label>

                        <label class="flex items-start gap-2.5 cursor-pointer group">
                            <input type="checkbox" id="agree_kvkk" name="agree_kvkk" value="1" required
                                class="mt-0.5 w-4 h-4 rounded border-slate-300 text-trendyol focus:ring-trendyol accent-trendyol shrink-0">
                            <span class="text-xs text-slate-600 font-semibold leading-relaxed">
                                <a href="{{ route('kvkk') }}" class="font-extrabold text-trendyol underline hover:text-trendyolDark" target="_blank">KVKK</a> ve
                                <a href="{{ route('procedural', 'gizlilik-politikasi') }}" class="font-extrabold text-trendyol underline hover:text-trendyolDark" target="_blank">Gizlilik Politikası</a>'nı okudum, onaylıyorum. *
                            </span>
                        </label>

                        <label class="flex items-start gap-2.5 cursor-pointer group">
                            <input type="checkbox" id="agree_accuracy" name="agree_accuracy" value="1" required
                                class="mt-0.5 w-4 h-4 rounded border-slate-300 text-trendyol focus:ring-trendyol accent-trendyol shrink-0">
                            <span class="text-xs text-slate-600 font-semibold leading-relaxed">
                                Girdiğim bilgilerin doğruluğundan <span class="font-extrabold text-slate-800">şahsım sorumludur</span>. *
                            </span>
                        </label>

                        <p class="text-[10px] text-slate-400">* işaretli onaylar zorunludur.</p>

                        {{-- Ticari elektronik ileti onayı — İSTEĞE BAĞLI ve
                             yukarıdaki zorunlu onaylardan ayrı. 6563 sayılı
                             kanun ayrı ve açık onay istiyor; önceden işaretli
                             kutu geçerli onay sayılmaz. Sipariş vermenin
                             şartı değildir, o yüzden ayrı bir kutuda. --}}
                        <div class="border-t border-slate-100 pt-3 mt-1 space-y-2.5">
                            <p class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Kampanya Bildirimleri (isteğe bağlı)</p>

                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="eposta_izni" value="1"
                                    class="mt-0.5 w-4 h-4 rounded border-slate-300 text-trendyol focus:ring-trendyol accent-trendyol shrink-0">
                                <span class="text-xs text-slate-600 font-semibold leading-relaxed">
                                    E-posta ile kampanya bildirimi almak istiyorum.
                                </span>
                            </label>

                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="sms_izni" value="1"
                                    class="mt-0.5 w-4 h-4 rounded border-slate-300 text-trendyol focus:ring-trendyol accent-trendyol shrink-0">
                                <span class="text-xs text-slate-600 font-semibold leading-relaxed">
                                    SMS / WhatsApp ile kampanya bildirimi almak istiyorum.
                                </span>
                            </label>

                            <p class="text-[10px] text-slate-400 leading-relaxed">
                                Onayınızı dilediğiniz an ücretsiz geri çekebilirsiniz. Sipariş ve kargo
                                bildirimleri bu onaydan bağımsız olarak gönderilir.
                            </p>
                        </div>
                    </div>

                    <button type="submit" id="pay-btn"
                        class="mt-6 w-full bg-trendyol hover:bg-trendyolDark text-white font-extrabold py-3.5 rounded-xl text-sm transition-all duration-200 hover:scale-[1.01] active:scale-95 shadow-lg flex items-center justify-center gap-2">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span id="pay-btn-text">Güvenli Ödemeye Geç</span>
                    </button>

                    <p class="text-[10px] text-center text-slate-400 mt-3 font-medium">
                        🔒 256-bit SSL ile korunmaktadır
                    </p>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@section('styles')
    <!-- Select2 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Ödeme yöntemi seçenekleri. Tailwind'in has-[:checked] varyantı
           yerine düz CSS: CDN sürümü sabitlenmemiş ve eski bir sürüme
           düşerse seçili kutu hiç vurgulanmazdı. */
        .odeme-secenegi {
            border-color: #E2E8F0; /* slate-200 */
        }
        .odeme-secenegi:hover {
            border-color: #CBD5E1; /* slate-300 */
        }
        .odeme-secenegi:has(input:checked) {
            border-color: #F27A1A; /* trendyol */
            background-color: #FFF7ED; /* orange-50 */
        }
        /* :has() desteklemeyen tarayıcılarda JS aynı sınıfı ekler. */
        .odeme-secenegi.secili {
            border-color: #F27A1A;
            background-color: #FFF7ED;
        }

        /* Modern Select2 Styling matching Tailwind CSS */
        .select2-container {
            width: 100% !important;
            margin-top: 0.25rem;
        }
        .select2-container--default .select2-selection--single {
            background-color: #F8FAFC !important; /* bg-slate-50 */
            border: 1px solid #E2E8F0 !important; /* border-slate-200 */
            border-radius: 0.5rem !important; /* rounded-lg */
            height: 42px !important;
            display: flex !important;
            align-items: center !important;
            padding-left: 0.5rem !important;
            padding-right: 1.5rem !important;
            transition: all 0.2s !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default .select2-selection--single:focus {
            background-color: #FFFFFF !important;
            border-color: #1B4A7A !important;
            box-shadow: 0 0 0 2px rgba(27, 74, 122, 0.15) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1E293B !important; /* text-slate-800 */
            font-size: 0.875rem !important; /* text-sm */
            font-weight: 500 !important;
            padding-left: 0.25rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94A3B8 !important; /* text-slate-400 */
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 0.5rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #64748B transparent transparent transparent !important;
            border-width: 5px 4px 0 4px !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #64748B transparent !important;
            border-width: 0 4px 5px 4px !important;
        }
        .select2-dropdown {
            background-color: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
            z-index: 9999 !important;
            margin-top: 4px !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #E2E8F0 !important;
            border-radius: 0.375rem !important;
            padding: 6px 10px !important;
            font-size: 0.875rem !important;
            outline: none !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #1B4A7A !important;
            box-shadow: 0 0 0 2px rgba(27, 74, 122, 0.1) !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #1B4A7A !important; /* bg-trendyol */
            color: #FFFFFF !important;
        }
        .select2-container--default .select2-results__option {
            padding: 8px 10px !important;
            font-size: 0.875rem !important;
            color: #334155 !important;
        }
        .select2-container--default .select2-selection--single[aria-disabled="true"] {
            background-color: #F1F5F9 !important; /* bg-slate-100 */
            border-color: #E2E8F0 !important;
            cursor: not-allowed !important;
        }
    </style>
@endsection

@section('scripts')
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        // Fatura aynı toggle
        function toggleBillingSection() {
            const toggle = document.getElementById('billing-same-toggle');
            const fields = document.getElementById('billing-address-fields');
            if (toggle.checked) {
                fields.classList.add('hidden');
                // Remove required attributes
                $('#billing_province_id').val(null).trigger('change').prop('required', false);
                $('#billing_district_id').val(null).trigger('change').prop('required', false);
                $('#billing_neighborhood_id').val(null).trigger('change').prop('required', false);
                const billingAddr = document.getElementById('billing_address_detail');
                if (billingAddr) billingAddr.removeAttribute('required');
            } else {
                fields.classList.remove('hidden');
                // Set required attributes
                const billingProvince = document.getElementById('billing_province_id');
                const billingDistrict = document.getElementById('billing_district_id');
                const billingNeighborhood = document.getElementById('billing_neighborhood_id');
                const billingAddr = document.getElementById('billing_address_detail');
                if (billingProvince) billingProvince.setAttribute('required', '');
                if (billingDistrict) billingDistrict.setAttribute('required', '');
                if (billingNeighborhood) billingNeighborhood.setAttribute('required', '');
                if (billingAddr) billingAddr.setAttribute('required', '');
            }
        }

        // Ticari fatura toggle
        function toggleCorporateFields() {
            const toggle = document.getElementById('corporate-invoice-toggle');
            const fields = document.getElementById('corporate-fields');
            if (toggle.checked) {
                fields.classList.remove('hidden');
                document.getElementById('company_name').setAttribute('required', '');
                document.getElementById('tax_number').setAttribute('required', '');
                document.getElementById('tax_office').setAttribute('required', '');
            } else {
                fields.classList.add('hidden');
                document.getElementById('company_name').removeAttribute('required');
                document.getElementById('tax_number').removeAttribute('required');
                document.getElementById('tax_office').removeAttribute('required');
            }

            // Ticari faturada TC/VKN her hâlükârda gerekir
            tcZorunluGuncelle();
        }

        /* ---------- Ödeme yöntemi: indirim satırı ve toplam ----------
           Sunucu ilk yüklemede seçili yönteme göre doğru tutarı basar; burada
           yalnızca kullanıcı yöntemi DEĞİŞTİRDİĞİNDE ekran güncellenir.
           Nihai tutar her hâlükârda sunucuda yeniden hesaplanır. */
        const HAVALE_YUZDE = {{ (float) $setting->bank_transfer_discount_percent }};
        const ARA_TOPLAM   = {{ round((float) ($subtotal ?? 0), 2) }};   // kupon sonrası, kargo hariç
        const KARGO        = {{ round((float) ($shippingCost ?? 0), 2) }};

        function tlYaz(tutar) {
            return tutar.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';
        }

        function odemeYontemiDegisti() {
            const secili  = document.querySelector('input[name="payment_type"]:checked');
            const havale  = secili && secili.value === 'bank_transfer';
            const indirim = havale ? Math.min(ARA_TOPLAM * HAVALE_YUZDE / 100, ARA_TOPLAM) : 0;

            const satir = document.getElementById('havale-indirim-satiri');
            if (satir) {
                satir.classList.toggle('hidden', indirim <= 0);
                document.getElementById('havale-indirim-tutari').textContent = '-' + tlYaz(indirim);
            }

            const toplam = document.getElementById('genel-toplam');
            if (toplam) {
                toplam.textContent = tlYaz(ARA_TOPLAM - indirim + KARGO);
            }

            const btnText = document.getElementById('pay-btn-text');
            if (btnText) {
                btnText.textContent = havale ? 'Siparişi Tamamla' : 'Güvenli Ödemeye Geç';
            }

            document.querySelectorAll('.odeme-secenegi').forEach(function (kutu) {
                kutu.classList.toggle('secili', !!kutu.querySelector('input:checked'));
            });

            tcZorunluGuncelle();
        }

        /* TC Kimlik No zorunluluğu ödeme yöntemine, ticari fatura tercihine ve
           tutara bağlı. Sunucu aynı kuralı yeniden uygular; buradaki yalnızca
           kullanıcıya doğru alanı göstermek içindir. */
        const TC_ESIK = {{ round((float) $setting->identity_required_threshold, 2) }};

        function tcZorunluGuncelle() {
            const secili  = document.querySelector('input[name="payment_type"]:checked');
            const kart    = secili && secili.value === 'card';
            const havale  = secili && secili.value === 'bank_transfer';
            const ticari  = document.getElementById('corporate-invoice-toggle')?.checked;
            const indirim = havale ? Math.min(ARA_TOPLAM * HAVALE_YUZDE / 100, ARA_TOPLAM) : 0;
            const toplam  = ARA_TOPLAM - indirim + KARGO;

            const zorunlu = !!(kart || ticari || TC_ESIK <= 0 || toplam >= TC_ESIK);

            const alan = document.getElementById('identity_number');
            if (alan) alan.required = zorunlu;

            document.getElementById('tc-yildiz')?.classList.toggle('hidden', !zorunlu);
            document.getElementById('tc-aciklama')?.classList.toggle('hidden', zorunlu);
            document.getElementById('tc-aciklama-zorunlu')?.classList.toggle('hidden', !zorunlu);
        }

        odemeYontemiDegisti();

        // Form submit handler with TC validation
        document.getElementById('checkout-form').addEventListener('submit', function (e) {
            const tcInput = document.getElementById('identity_number');
            // Zorunlu olmadığında boş bırakılabilir; yalnızca girilmişse
            // doğruluğu kontrol edilir.
            if (tcInput && (tcInput.required || tcInput.value.trim() !== '')) {
                const tcVal = tcInput.value.trim();
                if (!isValidTcKimlik(tcVal)) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Hata!',
                        text: 'Lütfen geçerli bir 11 haneli TC Kimlik No giriniz.',
                        icon: 'error',
                        confirmButtonText: 'Tamam',
                        confirmButtonColor: '#1B4A7A'
                    });
                    tcInput.focus();
                    return;
                }
            }
            
            const btn = document.getElementById('pay-btn');
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> İşleniyor...';
        });

        // TC Kimlik validation algorithm
        function isValidTcKimlik(tc) {
            if (tc.length !== 11) return false;
            if (!/^[1-9]\d{10}$/.test(tc)) return false;
            
            const d = tc.split('').map(Number);
            const oddSum = d[0] + d[2] + d[4] + d[6] + d[8];
            const evenSum = d[1] + d[3] + d[5] + d[7];
            
            const tenthDigit = ((oddSum * 7) - evenSum) % 10;
            if (d[9] !== tenthDigit) return false;
            
            const eleventhDigit = d.slice(0, 10).reduce((acc, curr) => acc + curr, 0) % 10;
            if (d[10] !== eleventhDigit) return false;
            
            return true;
        }

        // Phone Input format: +90 545 545 54 45
        function formatPhone(value) {
            let digits = value.replace(/\D/g, '');
            if (digits.startsWith('90')) {
                digits = digits.substring(2);
            } else if (digits.startsWith('0')) {
                digits = digits.substring(1);
            }
            
            digits = digits.substring(0, 10);
            if (digits.length === 0) return '';
            
            let formatted = '+90';
            if (digits.length > 0) {
                formatted += ' ' + digits.substring(0, 3);
            }
            if (digits.length > 3) {
                formatted += ' ' + digits.substring(3, 6);
            }
            if (digits.length > 6) {
                formatted += ' ' + digits.substring(6, 8);
            }
            if (digits.length > 8) {
                formatted += ' ' + digits.substring(8, 10);
            }
            return formatted;
        }

        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.maxLength = 17;
            const applyFormat = () => {
                phoneInput.value = formatPhone(phoneInput.value);
            };
            phoneInput.addEventListener('input', applyFormat);
            phoneInput.addEventListener('keydown', function(e) {
                if (phoneInput.selectionStart < 4 && (e.key === 'Backspace' || e.key === 'Delete')) {
                    e.preventDefault();
                }
            });
            applyFormat();
        }

        $(document).ready(function() {
            // Initialize Select2 dropdowns
            $('#province_id').select2({
                placeholder: 'İl Seçiniz',
                allowClear: true
            });
            $('#district_id').select2({
                placeholder: 'İlçe Seçiniz',
                allowClear: true
            });
            $('#neighborhood_id').select2({
                placeholder: 'Mahalle Seçiniz',
                allowClear: true
            });

            $('#billing_province_id').select2({
                placeholder: 'İl Seçiniz',
                allowClear: true
            });
            $('#billing_district_id').select2({
                placeholder: 'İlçe Seçiniz',
                allowClear: true
            });
            $('#billing_neighborhood_id').select2({
                placeholder: 'Mahalle Seçiniz',
                allowClear: true
            });

            // Delivery cascading dropdowns
            $('#province_id').on('change', function() {
                const provinceId = $(this).val();
                const $districtSelect = $('#district_id');
                const $neighborhoodSelect = $('#neighborhood_id');
                
                $districtSelect.html('<option value="">İlçe Seçiniz</option>').val(null).trigger('change').prop('disabled', true);
                $neighborhoodSelect.html('<option value="">Mahalle Seçiniz</option>').val(null).trigger('change').prop('disabled', true);
                
                if (!provinceId) return;
                
                $.getJSON("{{ route('location.districts') }}", { province_id: provinceId }, function(data) {
                    let options = '<option value="">Seçiniz</option>';
                    data.forEach(function(item) {
                        options += `<option value="${item.id}">${item.name}</option>`;
                    });
                    $districtSelect.html(options).prop('disabled', false).trigger('change');
                });
            });

            $('#district_id').on('change', function() {
                const districtId = $(this).val();
                const $neighborhoodSelect = $('#neighborhood_id');
                
                $neighborhoodSelect.html('<option value="">Mahalle Seçiniz</option>').val(null).trigger('change').prop('disabled', true);
                
                if (!districtId) return;
                
                $.getJSON("{{ route('location.neighborhoods') }}", { district_id: districtId }, function(data) {
                    let options = '<option value="">Seçiniz</option>';
                    data.forEach(function(item) {
                        options += `<option value="${item.id}">${item.name}</option>`;
                    });
                    $neighborhoodSelect.html(options).prop('disabled', false).trigger('change');
                });
            });

            /**
             * Kayıtlı adres seçimi.
             *
             * İl/ilçe/mahalle kademeli yükleniyor: ilçe listesi ancak il seçilince,
             * mahalle listesi ancak ilçe seçilince dolar. Bu yüzden değerleri
             * doğrudan atamak yetmez — her adımın AJAX'ını sırayla beklemek gerekir.
             */
            function ilceleriYukle(provinceId) {
                return $.getJSON("{{ route('location.districts') }}", { province_id: provinceId })
                    .then(function (data) {
                        let options = '<option value="">Seçiniz</option>';
                        data.forEach(i => options += `<option value="${i.id}">${i.name}</option>`);
                        $('#district_id').html(options).prop('disabled', false);
                    });
            }

            function mahalleleriYukle(districtId) {
                return $.getJSON("{{ route('location.neighborhoods') }}", { district_id: districtId })
                    .then(function (data) {
                        let options = '<option value="">Seçiniz</option>';
                        data.forEach(i => options += `<option value="${i.id}">${i.name}</option>`);
                        $('#neighborhood_id').html(options).prop('disabled', false);
                    });
            }

            $('.kayitli-adres').on('click', function () {
                const a = $(this).data('adres');
                const $btn = $(this);

                // Seçili adresi görsel olarak işaretle
                $('.kayitli-adres').removeClass('border-trendyol bg-white ring-2 ring-trendyol/20');
                $btn.addClass('border-trendyol bg-white ring-2 ring-trendyol/20');

                $('#first_name').val(a.first_name || '');
                $('#last_name').val(a.last_name || '');
                $('#phone').val(a.phone || '');
                $('#address_detail').val(a.address_detail || '');
                $('#zip_code').val(a.zip_code || '');

                if (!a.province_id) return;

                // Kademeli doldurma: il -> ilçe -> mahalle
                $('#province_id').val(a.province_id).trigger('change.select2');

                ilceleriYukle(a.province_id)
                    .then(function () {
                        if (!a.district_id) return;
                        $('#district_id').val(a.district_id).trigger('change.select2');
                        return mahalleleriYukle(a.district_id);
                    })
                    .then(function () {
                        if (!a.neighborhood_id) return;
                        $('#neighborhood_id').val(a.neighborhood_id).trigger('change.select2');
                    });
            });

            // Billing cascading dropdowns
            $('#billing_province_id').on('change', function() {
                const provinceId = $(this).val();
                const $districtSelect = $('#billing_district_id');
                const $neighborhoodSelect = $('#billing_neighborhood_id');
                
                $districtSelect.html('<option value="">İlçe Seçiniz</option>').val(null).trigger('change').prop('disabled', true);
                $neighborhoodSelect.html('<option value="">Mahalle Seçiniz</option>').val(null).trigger('change').prop('disabled', true);
                
                if (!provinceId) return;
                
                $.getJSON("{{ route('location.districts') }}", { province_id: provinceId }, function(data) {
                    let options = '<option value="">Seçiniz</option>';
                    data.forEach(function(item) {
                        options += `<option value="${item.id}">${item.name}</option>`;
                    });
                    $districtSelect.html(options).prop('disabled', false).trigger('change');
                });
            });

            $('#billing_district_id').on('change', function() {
                const districtId = $(this).val();
                const $neighborhoodSelect = $('#billing_neighborhood_id');
                
                $neighborhoodSelect.html('<option value="">Mahalle Seçiniz</option>').val(null).trigger('change').prop('disabled', true);
                
                if (!districtId) return;
                
                $.getJSON("{{ route('location.neighborhoods') }}", { district_id: districtId }, function(data) {
                    let options = '<option value="">Seçiniz</option>';
                    data.forEach(function(item) {
                        options += `<option value="${item.id}">${item.name}</option>`;
                    });
                    $neighborhoodSelect.html(options).prop('disabled', false).trigger('change');
                });
            });

            // Restore old inputs on validation redirect
            const oldProvinceId = "{{ old('province_id') }}";
            const oldDistrictId = "{{ old('district_id') }}";
            const oldNeighborhoodId = "{{ old('neighborhood_id') }}";

            if (oldProvinceId) {
                $('#province_id').val(oldProvinceId).trigger('change');
                
                $.getJSON("{{ route('location.districts') }}", { province_id: oldProvinceId }, function(data) {
                    let options = '<option value="">Seçiniz</option>';
                    data.forEach(function(item) {
                        const selected = (item.id == oldDistrictId) ? 'selected' : '';
                        options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                    });
                    $('#district_id').html(options).prop('disabled', false).trigger('change');
                    
                    if (oldDistrictId) {
                        $.getJSON("{{ route('location.neighborhoods') }}", { district_id: oldDistrictId }, function(data) {
                            let options = '<option value="">Seçiniz</option>';
                            data.forEach(function(item) {
                                const selected = (item.id == oldNeighborhoodId) ? 'selected' : '';
                                options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                            });
                            $('#neighborhood_id').html(options).prop('disabled', false).trigger('change');
                        });
                    }
                });
            }

            const oldBillingProvinceId = "{{ old('billing_province_id') }}";
            const oldBillingDistrictId = "{{ old('billing_district_id') }}";
            const oldBillingNeighborhoodId = "{{ old('billing_neighborhood_id') }}";

            if (oldBillingProvinceId) {
                $('#billing_province_id').val(oldBillingProvinceId).trigger('change');
                
                $.getJSON("{{ route('location.districts') }}", { province_id: oldBillingProvinceId }, function(data) {
                    let options = '<option value="">Seçiniz</option>';
                    data.forEach(function(item) {
                        const selected = (item.id == oldBillingDistrictId) ? 'selected' : '';
                        options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                    });
                    $('#billing_district_id').html(options).prop('disabled', false).trigger('change');
                    
                    if (oldBillingDistrictId) {
                        $.getJSON("{{ route('location.neighborhoods') }}", { district_id: oldBillingDistrictId }, function(data) {
                            let options = '<option value="">Seçiniz</option>';
                            data.forEach(function(item) {
                                const selected = (item.id == oldBillingNeighborhoodId) ? 'selected' : '';
                                options += `<option value="${item.id}" ${selected}>${item.name}</option>`;
                            });
                            $('#billing_neighborhood_id').html(options).prop('disabled', false).trigger('change');
                        });
                    }
                });
            }
        });

        function submitCoupon() {
            const input = document.getElementById('coupon-code-input');
            if (!input || !input.value.trim()) return;

            const code = input.value.trim().toUpperCase();
            const checkoutForm = document.getElementById('checkout-form');

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

            if (checkoutForm) {
                const inputs = checkoutForm.querySelectorAll('input, textarea, select');
                inputs.forEach(el => {
                    if (el.name && el.name !== '_token') {
                        const hiddenEl = document.createElement('input');
                        hiddenEl.type = 'hidden';
                        hiddenEl.name = el.name;
                        hiddenEl.value = el.type === 'checkbox' ? (el.checked ? el.value : '') : el.value;
                        form.appendChild(hiddenEl);
                    }
                });
            }

            document.body.appendChild(form);
            form.submit();
        }

        function submitRemoveCoupon() {
            const checkoutForm = document.getElementById('checkout-form');

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('coupon.remove') }}";

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = "{{ csrf_token() }}";
            form.appendChild(csrfToken);

            if (checkoutForm) {
                const inputs = checkoutForm.querySelectorAll('input, textarea, select');
                inputs.forEach(el => {
                    if (el.name && el.name !== '_token') {
                        const hiddenEl = document.createElement('input');
                        hiddenEl.type = 'hidden';
                        hiddenEl.name = el.name;
                        hiddenEl.value = el.type === 'checkbox' ? (el.checked ? el.value : '') : el.value;
                        form.appendChild(hiddenEl);
                    }
                });
            }

            document.body.appendChild(form);
            form.submit();
        }
    </script>
@endsection
