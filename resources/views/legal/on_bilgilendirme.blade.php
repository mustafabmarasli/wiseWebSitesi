@extends('layouts.app')

@section('title', 'Ön Bilgilendirme Formu - Wise Solutions')
@section('meta_description', 'Mesafeli satış işlemi öncesinde alıcının bilgilendirilmesine ilişkin yasal formdur.')

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Anasayfa</a>
            <span>/</span>
            <span class="text-slate-700">Ön Bilgilendirme Formu</span>
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
                <p class="mt-1 text-amber-700/90 leading-relaxed">Bu ön bilgilendirme formu Türkiye tüketici mevzuatına uyum amacıyla taslak olarak hazırlanmıştır. Ödeme altyapınızı canlıya almadan önce yasal danışmanınız veya bir avukat tarafından incelenerek onaylanması tavsiye edilir.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-12 prose prose-slate max-w-none">
            
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2 pb-4 border-b border-slate-100" id="obf-title">
                Ön Bilgilendirme Formu
            </h1>

            <p class="text-xs text-slate-400 font-bold mb-8">Son Güncelleme: 25 Temmuz 2026</p>

            <div class="space-y-6 text-sm text-slate-600 leading-relaxed font-medium">
                
                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">1. Satıcı Bilgileri</h2>
                    <p>Sözleşme konusu malı veya hizmeti sunan tüzel kişinin bilgileri şu şekildedir:</p>
                    <div class="mt-2 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <ul class="list-disc pl-5 space-y-1">
                            <li><strong>Unvan:</strong> Wise Solutions Bilgi Teknolojileri Paz. ve Tic. Ltd. Şti.</li>
                            <li><strong>Adres:</strong> Melikgazi / Kayseri</li>
                            <li><strong>E-posta:</strong> info@wisesolutions.com.tr</li>
                        </ul>
                    </div>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">2. Alıcı Bilgileri</h2>
                    <p>Alıcı, sipariş aşamasında sisteme üye olan veya sipariş verirken iletişim ve teslimat adres bilgilerini beyan eden tüketicidir. İlgili bilgilerin doğruluğu Alıcı'nın sorumluluğundadır.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">3. Sözleşme Konusu Malın/Hizmetin Nitelikleri ve Fiyatı</h2>
                    <p>Satın alınan ürünlerin cinsi, kodu, adedi, satış fiyatı, KDV oranları ve kargo ücretleri sipariş vermeden hemen önce sepet sayfasında ve ödeme onay ekranında Alıcı'nın onayına sunulur. Ürünlerin toplam bedeline KDV dahildir.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">4. Teslimat ve Kargo Süreci</h2>
                    <p>Sipariş konusu ürünler, yasal 30 günlük süreyi aşmamak koşuluyla, Alıcı'nın sipariş formunda belirttiği teslimat adresine gönderilir. Teslimat kargo ücreti, aksi sipariş aşamasında kampanya kapsamında belirtilmediği sürece Alıcı'ya aittir ve faturaya yansıtılır.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">5. Cayma Hakkı Bilgilendirmesi</h2>
                    <p>Tüketici, hiçbir gerekçe göstermeksizin ve cezai şart ödemeksizin, satın aldığı malı teslim aldığı tarihten itibaren <strong>14 (on dört) gün</strong> içinde cayma hakkını kullanarak iade edebilir. Cayma hakkının kullanılması için Satıcı'ya yazılı veya e-posta ile bildirim yapılması gerekmektedir.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">6. Cayma Hakkı İstisnaları (İade Edilemeyen Ürünler)</h2>
                    <p>Aşağıdaki ürün gruplarında cayma hakkı kullanılamaz:</p>
                    <ul class="list-disc pl-5 mt-2 space-y-2">
                        <li>Ambalajı, mührü veya koruyucu bantları açılmış olan, tesliminden sonra hijyen kuralları ve sağlık koruma yönünden iadesi uygun olmayan tıbbi aparatlar ve kişisel lens vantuzları (Sağlık & Lens bölümü ürünleri).</li>
                        <li>Ambalajı açılmış, fiziksel temas kurulmuş, lehimlenmiş veya enerji verilerek test edilmiş hassas elektronik kartlar ve modüller.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">7. Şikayet ve İtirazlar</h2>
                    <p>Tüketiciler, şikayet ve itirazları konusundaki başvurularını Gümrük ve Ticaret Bakanlığı tarafından her yıl belirlenen yasal sınırlar dâhilinde mal veya hizmeti satın aldıkları veya ikametgâhlarının bulunduğu yerdeki Tüketici Hakem Heyetlerine veya Tüketici Mahkemelerine yapabilirler.</p>
                </section>

            </div>

        </div>
    </div>

@endsection
