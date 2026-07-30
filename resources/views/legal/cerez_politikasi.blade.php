@extends('layouts.app')

@section('title', 'Çerez Politikası - Wise Solutions')
@section('meta_description', 'Sitemizde kullanılan zorunlu, fonksiyonel ve analitik çerezlerin çalışma şeklini, çerezleri nasıl kontrol edeceğinizi açıklar.')

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3">
        <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Anasayfa</a>
            <span>/</span>
            <span class="text-slate-700">Çerez Politikası</span>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
        
        <!-- Legal Disclaimer Alert -->
        <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-xl p-4 mb-8 text-xs sm:text-sm font-semibold flex items-start gap-3 shadow-sm">
            <svg class="h-5 w-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="font-bold text-amber-800">⚠️ Hukuki Uyarı & Taslak Bildirimi</p>
                <p class="mt-1 text-amber-700/90 leading-relaxed">Bu çerez politikası web sitesi çerez kullanımına uyum amacıyla taslak olarak hazırlanmıştır. Yayına almadan önce yasal danışmanınız veya bir avukat tarafından incelenmesi tavsiye edilir.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-12 prose prose-slate max-w-none">
            
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2 pb-4 border-b border-slate-100" id="cp-title">
                Çerez Politikası
            </h1>

            <p class="text-xs text-slate-400 font-bold mb-8">Son Güncelleme: 25 Temmuz 2026</p>

            <div class="space-y-6 text-sm text-slate-600 leading-relaxed font-medium">
                
                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">1. Çerez (Cookie) Nedir?</h2>
                    <p>Çerezler, ziyaret ettiğiniz web siteleri tarafından bilgisayarınıza veya mobil cihazınıza kaydedilen küçük boyutlu metin dosyalarıdır. Çerezler sitenin daha verimli çalışmasını sağlamak, kişiselleştirilmiş bir deneyim sunmak ve site sahiplerine analitik bilgiler sunmak amacıyla yaygın olarak kullanılır.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">2. Kullanılan Çerez Türleri</h2>
                    <p>Sitemizde kullanıcı deneyimini iyileştirmek için aşağıdaki kategorilerde çerezler kullanılmaktadır:</p>
                    <ul class="list-disc pl-5 mt-2 space-y-3">
                        <li><strong>Zorunlu Çerezler:</strong> Web sitesinin temel fonksiyonlarının çalışması (sepetinizi hatırlama, güvenli POS bağlantısının kurulması ve oturum yönetimi) için zorunlu olarak yüklenen çerezlerdir. Devre dışı bırakılamazlar.</li>
                        <li><strong>Fonksiyonel Çerezler:</strong> Dil tercihiniz, çerez onay durumunuz gibi tercihlerinizi kaydederek sitenin bir sonraki ziyaretinizde sizi hatırlamasını sağlayan çerezlerdir.</li>
                        <li><strong>Analitik ve Performans Çerezleri:</strong> Sitemizi kaç kişinin ziyaret ettiğini, hangi sayfaların daha çok tıklandığını analiz ederek site performansını ölçmemize yardımcı olan anonim çerezlerdir (Kullanıcı onayı verilmeden bu çerezler yüklenmez).</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">3. Çerez Tercihlerini Yönetme</h2>
                    <p>Web sitemize ilk girdiğinizde karşınıza çıkan çerez onay banner'ında **Kabul Et** seçeneğine tıklayarak analitik çerezlerin çalışmasına onay verebilir veya **Reddet** seçeneği ile yalnızca zorunlu çerezlerin çalışmasını sağlayabilirsiniz. Dilediğiniz zaman tarayıcınızın ayarlarından da çerezleri tamamen silebilir ya da engelleyebilirsiniz.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">4. İletişim</h2>
                    <p>Çerez politikamız ile ilgili sorularınız için bizimle iletişime geçebilirsiniz:</p>
                    <p class="mt-1 font-bold text-slate-800">E-posta: info@wisesolutions.com.tr</p>
                </section>

            </div>

        </div>
    </div>

@endsection
