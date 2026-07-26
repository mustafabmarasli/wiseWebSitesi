@extends('layouts.app')

@section('title', 'Dış Ticaret ve Danışmanlık Hizmetleri - Wise Solutions')
@section('meta_description', 'İthalat, ihracat, küresel ürün tedariği ve gümrük süreçleri yönetimi konularında profesyonel danışmanlık hizmetlerimiz.')

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Giriş Portalı</a>
            <span>/</span>
            <span class="text-slate-700">Dış Ticaret & Danışmanlık</span>
        </div>
    </div>

    <!-- Hero Header -->
    <div class="bg-slate-900 text-white py-16 sm:py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center opacity-25" style="background-image: url('https://images.unsplash.com/photo-1578575437130-527eed3abbec?auto=format&fit=crop&w=1200&q=80');"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span class="bg-trendyol text-white text-xs font-extrabold uppercase tracking-widest px-3 py-1 rounded-full">Küresel Çözümler</span>
            <h1 class="text-3xl sm:text-5xl font-black mt-4 mb-4 tracking-tight">Dış Ticaret ve Danışmanlık</h1>
            <p class="text-slate-300 text-sm sm:text-lg max-w-2xl mx-auto leading-relaxed">Şirketinizin küresel pazarda büyümesi, ürün tedariği, gümrük süreçleri ve lojistik optimizasyonunda profesyonel yol arkadaşınızız.</p>
        </div>
    </div>

    <!-- Services Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16">
        <div class="text-center mb-12">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Hizmet Alanlarımız</h2>
            <p class="text-slate-500 text-sm mt-2">Dış ticarette risklerinizi azaltan, büyümenizi hızlandıran uzman destek.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Service 1 -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8 hover:shadow-md transition-shadow">
                <div class="bg-orange-50 w-12 h-12 rounded-xl flex items-center justify-center text-trendyol mb-6">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9-9c1.657 0 3 4.03 3 9s-1.343 9-3 9m0-18c-1.657 0-3 4.03-3 9s1.343 9 3 9m-9-9a9 9 0 019-9" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-3">İthalat & İhracat Yönetimi</h3>
                <p class="text-slate-500 text-xs sm:text-sm leading-relaxed">Hedef pazarların analizi, potansiyel alıcı/satıcı tespiti ve sözleşme süreçlerinin uluslararası mevzuata uygun şekilde yürütülmesi.</p>
            </div>

            <!-- Service 2 -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8 hover:shadow-md transition-shadow">
                <div class="bg-indigo-50 w-12 h-12 rounded-xl flex items-center justify-center text-indigo-500 mb-6">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10M13 16h6l1.286-6H13m6 6h1.714C21.433 16 22 15.433 22 14.857V12l-2-2.5" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-3">Lojistik ve Tedarik Zinciri</h3>
                <p class="text-slate-500 text-xs sm:text-sm leading-relaxed">Ürünlerinizin fabrikadan teslim alınıp, kapınıza veya müşterinizin deposuna kadar ulaşacağı lojistik zincirinin en verimli maliyetlerle planlanması.</p>
            </div>

            <!-- Service 3 -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sm:p-8 hover:shadow-md transition-shadow">
                <div class="bg-emerald-50 w-12 h-12 rounded-xl flex items-center justify-center text-emerald-500 mb-6">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-3">Yasal Mevzuat & Akreditif</h3>
                <p class="text-slate-500 text-xs sm:text-sm leading-relaxed">Uluslararası ödeme şekilleri (akreditif vb.), gümrük vergileri muafiyeti, teşvikler ve dış ticarette karşılaşılabilecek tüm bürokratik engellerin aşılması.</p>
            </div>

        </div>
    </div>

    <!-- Request a Consultation Form -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-20 mb-16">
        <div class="bg-slate-900 text-white rounded-3xl p-8 sm:p-12 relative overflow-hidden shadow-xl border border-slate-800">
            <div class="absolute inset-0 bg-gradient-to-r from-trendyol/10 to-transparent"></div>
            
            <div class="relative z-10 max-w-xl mx-auto text-center mb-8">
                <h2 class="text-xl sm:text-3xl font-extrabold tracking-tight">Hizmetlerimiz Hakkında Bilgi Alın</h2>
                <p class="text-slate-400 text-xs sm:text-sm mt-2">Dış ticaret sorularınızı uzman ekibimizle paylaşın, size özel çözümler sunalım.</p>
            </div>

            <form action="{{ route('contact.submit') }}" method="POST" class="relative z-10 space-y-4 max-w-2xl mx-auto text-slate-800">
                @csrf
                <input type="hidden" name="subject" value="Dış Ticaret & Danışmanlık Bilgi Talebi">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        placeholder="Adınız Soyadınız" 
                        class="w-full bg-slate-800 border border-slate-700 text-white placeholder-slate-500 rounded-lg text-xs sm:text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-slate-900 transition-all"
                    >
                    <input 
                        type="email" 
                        name="email" 
                        required 
                        placeholder="E-Posta Adresiniz" 
                        class="w-full bg-slate-800 border border-slate-700 text-white placeholder-slate-500 rounded-lg text-xs sm:text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-slate-900 transition-all"
                    >
                </div>
                
                <textarea 
                    name="message" 
                    rows="4" 
                    required 
                    placeholder="Danışmak istediğiniz konuyu yazınız..." 
                    class="w-full bg-slate-800 border border-slate-700 text-white placeholder-slate-500 rounded-lg text-xs sm:text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-trendyol focus:bg-slate-900 transition-all resize-none"
                ></textarea>

                <div class="flex items-start gap-2 pt-2">
                    <input type="checkbox" id="consulting-consent" required class="h-4 w-4 text-trendyol border-slate-700 rounded focus:ring-trendyol bg-slate-800 mt-0.5">
                    <label for="consulting-consent" class="text-[10px] text-slate-400 font-semibold leading-normal">
                        Gönderdiğim verilerin iletişim süreçlerinde kullanılmasına onay veriyorum.
                    </label>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-trendyol hover:bg-trendyolDark text-white py-3 rounded-lg font-extrabold text-sm transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-1.5"
                    id="btn-consulting-submit"
                >
                    <span>Danışmanlık Talebi Gönder</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Bize Ulaşın Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mb-20 font-sans">
        <div class="bg-white rounded-3xl p-8 sm:p-10 border border-slate-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex gap-4 items-center">
                <div class="w-12 h-12 bg-sky-50 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="h-6 w-6 text-trendyol" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900">Bize Ulaşın</h3>
                    <p class="text-slate-500 text-xs sm:text-sm font-semibold">Tüm kurumsal danışmanlık ve ithalat/ihracat sorularınız için bize yazın.</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-8 w-full md:w-auto shrink-0 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-8 text-xs sm:text-sm">
                <div>
                    <span class="text-slate-400 font-extrabold block">Kurumsal E-posta</span>
                    <a href="mailto:info@wisesolutions.com.tr" class="text-trendyol hover:underline font-extrabold">info@wisesolutions.com.tr</a>
                </div>
                <div>
                    <span class="text-slate-400 font-extrabold block">Çalışma Saatleri</span>
                    <span class="text-slate-700 font-extrabold">Hafta İçi (Pzt-Cum): 09:00 - 18:00</span>
                </div>
            </div>
        </div>
    </div>

@endsection
