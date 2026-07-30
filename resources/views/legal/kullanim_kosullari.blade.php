@extends('layouts.app')

@section('title', 'Kullanım Koşulları - Wise Solutions')
@section('meta_description', 'Web sitemizi kullanırken uymanız gereken kuralları, içeriklerin telif haklarını ve yasal sorumlulukları içerir.')

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3">
        <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Anasayfa</a>
            <span>/</span>
            <span class="text-slate-700">Kullanım Koşulları</span>
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
                <p class="mt-1 text-amber-700/90 leading-relaxed">Bu kullanım koşulları web sitesi kullanımı ve yasal sorumlulukların dağılımı amacıyla taslak olarak hazırlanmıştır. Yayına almadan önce bir avukat tarafından onaylanması tavsiye edilir.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-12 prose prose-slate max-w-none">
            
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2 pb-4 border-b border-slate-100" id="kk-title">
                Kullanım Koşulları
            </h1>

            <p class="text-xs text-slate-400 font-bold mb-8">Son Güncelleme: 25 Temmuz 2026</p>

            <div class="space-y-6 text-sm text-slate-600 leading-relaxed font-medium">
                
                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">1. Kabul ve Koşullar</h2>
                    <p>Bu web sitesini ziyaret ederek veya sitemizden alışveriş yaparak, işbu Kullanım Koşulları'nda yer alan tüm maddeleri peşinen kabul etmiş sayılırsınız. Koşulları kabul etmiyorsanız lütfen siteyi kullanmaya devam etmeyiniz.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">2. Fikri Mülkiyet Hakları</h2>
                    <p>Sitede yayınlanan tüm içerikler, ürün açıklamaları, görseller, kodlar, tasarımlar, logolar ve şemalar **Wise Solutions Bilgi Teknolojileri Paz. ve Tic. Ltd. Şti.** adına kayıtlıdır ve yasal koruma altındadır. Bu materyallerin yazılı izin alınmaksızın kopyalanması, çoğaltılması veya ticari amaçlarla başka platformlarda kullanılması yasaktır.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">3. Teknik Bilgi ve Sorumluluk Sınırı</h2>
                    <p>Elektronik kategorisindeki ürünler için sağlanan devre şemaları, pin açıklamaları ve teknik bilgiler sadece kılavuz niteliğindedir. Alıcı'nın bu bileşenleri hatalı bağlantı, yanlış besleme gerilimi veya elektrostatik deşarj nedeniyle bozması durumunda Wise Solutions sorumlu tutulamaz.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">4. Sağlık Ürünleri Bilgilendirmesi</h2>
                    <p>Sağlık & Lens bölümünde satılan aparatlar ve DMV vantuzlar tıbbi bir teşhis veya tedavi aracı değildir. Kullanıcılar aparatları kendi sorumluluklarında kullanırlar ve kullanım öncesinde bir göz doktoruna danışmaları önerilir.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">5. Koşullarda Değişiklik</h2>
                    <p>Wise Solutions, dilediği zaman bu kullanım koşullarında değişiklik yapma hakkını saklı tutar. Güncel koşullar sitede yayınlandığı andan itibaren geçerlilik kazanır.</p>
                </section>

            </div>

        </div>
    </div>

@endsection
