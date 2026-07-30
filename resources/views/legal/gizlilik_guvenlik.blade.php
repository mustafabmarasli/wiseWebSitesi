@extends('layouts.app')

@section('title', 'Gizlilik ve Güvenlik Politikası - Wise Solutions')
@section('meta_description', 'Müşterilerimizin kişisel verilerinin gizliliği, kredi kartı güvenlik altyapımız (SSL ve iyzico) hakkında bilgilendirmedir.')

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3">
        <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Anasayfa</a>
            <span>/</span>
            <span class="text-slate-700">Gizlilik ve Güvenlik Politikası</span>
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
                <p class="mt-1 text-amber-700/90 leading-relaxed">Bu gizlilik politikası web sitesi güvenliği ve veri gizliliğine uyum amacıyla taslak olarak hazırlanmıştır. Yayına almadan önce yasal temsilciniz veya bir avukat tarafından onaylanması tavsiye edilir.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-12 prose prose-slate max-w-none">
            
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2 pb-4 border-b border-slate-100" id="ggp-title">
                Gizlilik ve Güvenlik Politikası
            </h1>

            <p class="text-xs text-slate-400 font-bold mb-8">Son Güncelleme: 25 Temmuz 2026</p>

            <div class="space-y-6 text-sm text-slate-600 leading-relaxed font-medium">
                
                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">1. Genel Bilgilendirme</h2>
                    <p>Wise Solutions Bilgi Teknolojileri Paz. ve Tic. Ltd. Şti. (Melikgazi/Kayseri) olarak, müşterilerimizin ve sitemizi ziyaret eden kullanıcılarımızın gizliliğini korumak ve güvenliğini en üst seviyede tutmak birinci önceliğimizdir. Bu politika kapsamında sitemizde toplanan verilerin güvenlik standartları ve kullanım ilkeleri açıklanmaktadır.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">2. Ödeme ve Kart Güvenliği (SSL & iyzico)</h2>
                    <p>Alışverişleriniz sırasında ödeme işlemlerinizin tam güvenlikle gerçekleşmesi amacıyla aşağıdaki önlemler alınmıştır:</p>
                    <ul class="list-disc pl-5 mt-2 space-y-2">
                        <li><strong>SSL Sertifikalı Güvenlik:</strong> Sitemiz üzerindeki tüm bilgi akışı 256-bit SSL şifreleme protokolü ile korunmaktadır. Bilgileriniz tarayıcınızdan doğrudan ödeme sunucularına şifrelenmiş olarak iletilir.</li>
                        <li><strong>Sanal POS Altyapısı:</strong> Sitemizde kredi kartı ödemeleri yetkili ödeme kuruluşu **iyzico** Sanal POS altyapısı aracılığıyla tahsil edilir.</li>
                        <li><strong>Kart Bilgileri Depolama Politikası:</strong> Kredi kartı veya banka kartı şifre/numara bilgileriniz hiçbir şekilde bizim sunucularımızda **tutulmaz, kaydedilmez ve saklanmaz**. Ödeme akışı tamamen iyzico'nun BDDK lisanslı güvenli ödeme ekranları üzerinden gerçekleştirilir.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">3. Kişisel Verilerin Güvenliği</h2>
                    <p>Sipariş ve fatura süreçlerinin yönetimi için paylaştığınız ad, soyad, e-posta, teslimat adresi ve telefon numarası gibi kişisel verileriniz, izniniz olmadan üçüncü şahıslara veya kurumlara ticari, reklam ya da pazarlama amacıyla aktarılmaz. Güvenlik ihlallerini önlemek adına tüm verilerimiz yetkisiz erişimlere karşı korumalı veri tabanlarında saklanmaktadır.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">4. İletişim Bilgileri</h2>
                    <p>Gizlilik ve güvenlik uygulamalarımız hakkında sorularınız olması halinde bizimle e-posta yoluyla irtibata geçebilirsiniz:</p>
                    <p class="mt-1 font-bold text-slate-800">E-posta: info@wisesolutions.com.tr</p>
                </section>

            </div>

        </div>
    </div>

@endsection
