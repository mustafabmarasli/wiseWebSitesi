@extends('layouts.app')

@section('title', 'Teslimat ve İade Politikası - Wise Solutions')
@section('meta_description', 'Satın aldığınız ürünlerin kargo teslimat süreleri, cayma hakkı kapsamı ve iade koşullarımız hakkında detaylı bilgilendirmedir.')

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Anasayfa</a>
            <span>/</span>
            <span class="text-slate-700">Teslimat ve İade Politikası</span>
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
                <p class="mt-1 text-amber-700/90 leading-relaxed">Bu teslimat ve iade politikası Türkiye tüketici kanunlarına uygun taslak olarak hazırlanmıştır. Yayına almadan önce bir avukat tarafından onaylanması tavsiye edilir.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-12 prose prose-slate max-w-none">
            
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2 pb-4 border-b border-slate-100" id="tip-title">
                Teslimat ve İade Politikası
            </h1>

            <p class="text-xs text-slate-400 font-bold mb-8">Son Güncelleme: 25 Temmuz 2026</p>

            <div class="space-y-6 text-sm text-slate-600 leading-relaxed font-medium">
                
                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">1. Teslimat Koşulları</h2>
                    <p>Sitemiz üzerinden verdiğiniz siparişler, ödeme onayının ardından en geç 3 (üç) iş günü içerisinde anlaşmalı kargo firmamıza teslim edilir. Sipariş kargoya teslim edildiğinde, tarafınıza e-posta veya SMS yoluyla gönderilecek takip numarası ile gönderinizin durumunu takip edebilirsiniz. Kargo gönderim bedeli sipariş esnasında faturanıza yansıtılmaktadır.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">2. İade ve Cayma Hakkı (14 Gün)</h2>
                    <p>Alıcı, satın aldığı malı teslim aldığı tarihten itibaren <strong>14 (on dört) gün</strong> içerisinde hiçbir gerekçe göstermeksizin ve cezai şart ödemeksizin iade etme hakkına (cayma hakkı) sahiptir. İade edilecek ürünlerin kargo masrafları, anlaşmalı kargo firmamız kullanıldığı sürece Satıcı'ya aittir.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">3. İade Kabul Edilemeyen Özel Durumlar (İstisnalar)</h2>
                    <p>Ürünlerimizin hassas yapısı gereği, aşağıdaki durumlarda kanuni olarak iade kabul edilmemektedir:</p>
                    
                    <div class="mt-3 bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-3 text-slate-700">
                        <p><strong>🩺 Sağlık & Hijyen Koşulu (Scleral Plunger ve Lens Aparatları):</strong></p>
                        <p class="text-xs text-slate-500 leading-relaxed font-semibold">Tüketicinin Korunması Hakkında Kanun gereğince; koruyucu ambalajı, vakum contası, mühür veya bantları açılmış olan tıbbi ürünlerin, kişisel sağlık ve hijyen aparatlarının (Scleral Plungers, lens saklama kutuları, vantuzlar) ambalajı açıldıktan sonra **iadesi yasal olarak imkansızdır** ve kabul edilmez.</p>

                        <p><strong>🔌 Hassas Elektronik Donanımlar (Geliştirme Kartları ve LED Şeritler):</strong></p>
                        <p class="text-xs text-slate-500 leading-relaxed font-semibold">Statik elektrik hasarı (ESD) riski, kısa devre, enerji verme, lehimleme veya fiziksel pin modifikasyonlarına karşı korumak amacıyla; antistatik ambalajı yırtılmış veya kartı çalıştırılmış olan ESP32, ESP8266, regülatör ve şerit LED ürünleri iade kapsamı dışındadır. Sadece orijinal ambalajı açılmamış ve test edilmemiş paketler 14 gün içinde iade edilebilir.</p>
                    </div>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">4. İade Süreci</h2>
                    <p>İade etmek istediğiniz ambalajı açılmamış hasarsız ürünleri, faturasıyla birlikte ve iade nedeninizi belirten bir not ile **info@wisesolutions.com.tr** adresimize bildirerek kargolayabilirsiniz. İade kabul edildikten sonra 10 gün içinde ödemeniz kredi kartınıza iade edilir.</p>
                </section>

            </div>

        </div>
    </div>

@endsection
