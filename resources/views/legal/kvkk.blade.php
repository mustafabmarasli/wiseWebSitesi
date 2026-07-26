@extends('layouts.app')

@section('title', 'KVKK Aydınlatma Metni - Wise Solutions')
@section('meta_description', 'Kişisel Verilerin Korunması Kanunu (KVKK) kapsamında, kişisel verilerinizin işlenme amaçları, aktarımı ve haklarınız hakkında aydınlatma metnidir.')

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Anasayfa</a>
            <span>/</span>
            <span class="text-slate-700">KVKK Aydınlatma Metni</span>
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
                <p class="mt-1 text-amber-700/90 leading-relaxed">Bu KVKK metni Türkiye'deki 6698 Sayılı Kanun kapsamına uyum amacıyla taslak olarak hazırlanmıştır. Kişisel veri toplama yöntemlerinize ve şirketinizin iç politikalarına göre yasal geçerlilik kazanması için hukuk danışmanınız veya bir avukat tarafından incelenerek onaylanması tavsiye edilir.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-12 prose prose-slate max-w-none">
            
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2 pb-4 border-b border-slate-100" id="kvkk-title">
                Kişisel Verilerin Korunması Kanunu (KVKK) Aydınlatma Metni
            </h1>

            <p class="text-xs text-slate-400 font-bold mb-8">Son Güncelleme: 25 Temmuz 2026</p>

            <div class="space-y-6 text-sm text-slate-600 leading-relaxed font-medium">
                
                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">1. Veri Sorumlusu</h2>
                    <p>6698 sayılı Kişisel Verilerin Korunması Kanunu (“KVKK”) uyarınca, kişisel verileriniz; veri sorumlusu olarak <strong>Wise Solutions Bilgi Teknolojileri Paz. ve Tic. Ltd. Şti. (Melikgazi / Kayseri)</strong> (“Şirket” veya “Wise Solutions”) tarafından aşağıda açıklanan kapsamda işlenebilecektir.</p>
                    <p class="mt-2 text-xs text-slate-500 font-semibold">
                        Şirket Adresi: Melikgazi / Kayseri
                    </p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">2. Kişisel Verilerinizin İşlenme Amacı</h2>
                    <p>Toplanan kişisel verileriniz, aşağıdaki amaçlarla (“Amaçlar”) KVKK’nın 5. ve 6. maddelerinde belirtilen kişisel veri işleme şartları dahilinde işlenebilecektir:</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>Şirketimiz tarafından sunulan mikroişlemci kartı, LED aydınlatma ve tıbbi lens aparatlarının satış ve teslimat süreçlerinin yürütülmesi,</li>
                        <li>Siparişlerinizin faturalandırılması ve mali yükümlülüklerin yerine getirilmesi,</li>
                        <li>Müşteri destek, soru, öneri ve taleplerinizin cevaplanması amacıyla iletişim formu verilerinin işlenmesi,</li>
                        <li>Web sitesi kullanım deneyiminizin iyileştirilmesi, yasal gerekliliklerin ve iyzico/sanal pos ödeme güvenliğinin sağlanması.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">3. İşlenen Kişisel Verileriniz</h2>
                    <p>Sitemiz üzerinden gerçekleştirdiğiniz alışverişlerde veya bizimle iletişime geçtiğinizde işlenen verileriniz şunlardır:</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li><strong>Kimlik Bilgisi:</strong> Adınız, soyadınız.</li>
                        <li><strong>İletişim Bilgisi:</strong> E-posta adresiniz, telefon numaranız, teslimat ve fatura adresiniz.</li>
                        <li><strong>İşlem Güvenliği Bilgisi:</strong> IP adresiniz, sipariş geçmişiniz, sepet hareketleriniz, çerez onay tercihiniz.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">4. Kişisel Verilerinizin Aktarılması</h2>
                    <p>İşlenen kişisel verileriniz; yukarıda belirtilen Amaçlar doğrultusunda, kargo teslimat firmalarına (teslimat için), iyzico/ödeme aracı kuruluşlarına (sanal pos ödeme işlemleri için), muhasebe yazılımlarımıza ve yasal bildirim zorunluluğu kapsamında yetkili kamu kurum ve kuruluşlarına aktarılabilecektir. Üçüncü şahıslara reklam veya pazarlama amacıyla asla veri satışı yapılmamaktadır.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">5. Kişisel Veri Sahibinin Hakları (KVKK Madde 11)</h2>
                    <p>Kişisel veri sahibi olarak KVKK’nın 11. maddesi uyarınca aşağıdaki haklara sahipsiniz:</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        <li>Kişisel verilerinizin işlenip işlenmediğini öğrenme,</li>
                        <li>Kişisel verileriniz işlenmişse buna ilişkin bilgi talep etme,</li>
                        <li>Kişisel verilerinizin işlenme amacını ve bunların amacına uygun kullanılıp kullanılmadığını öğrenme,</li>
                        <li>Yurt içinde veya yurt dışında kişisel verilerinizin aktarıldığı üçüncü kişileri bilme,</li>
                        <li>Kişisel verilerinizin eksik veya yanlış işlenmiş olması hâlinde bunların düzeltilmesini isteme,</li>
                        <li>KVKK ve ilgili diğer kanun hükümlerine uygun olarak işlenmiş olmasına rağmen, işlenmesini gerektiren sebeplerin ortadan kalkması hâlinde kişisel verilerinizin silinmesini veya yok edilmesini isteme.</li>
                    </ul>
                </section>

                <section class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <p class="font-bold text-slate-800">Başvuru Yöntemi:</p>
                    <p class="text-xs text-slate-500 mt-1">Haklarınızı kullanmak için taleplerinizi yazılı olarak veya kayıtlı elektronik posta (KEP) adresi, güvenli elektronik imza vasıtasıyla firmamıza iletebilirsiniz. Detaylı bilgi veya iletişim için <a href="{{ route('contact') }}" class="text-trendyol hover:underline">İletişim Sayfamızı</a> kullanabilirsiniz.</p>
                </section>

            </div>

        </div>
    </div>

@endsection
