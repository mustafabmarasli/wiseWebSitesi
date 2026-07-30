@extends('layouts.app')

@section('title', 'Mesafeli Satış Sözleşmesi - Wise Solutions')
@section('meta_description', 'Sitemiz üzerinden yapacağınız mesafeli alışverişlerin yasal koşullarını, cayma hakkını ve satıcı yükümlülüklerini içeren sözleşmedir.')

@section('content')

    <!-- Breadcrumb -->
    <div class="bg-slate-100 py-3">
        <div class="max-w-site mx-auto px-4 sm:px-6 lg:px-8 text-xs font-semibold text-slate-500 flex gap-2 items-center">
            <a href="{{ route('landing') }}" class="hover:text-trendyol">Anasayfa</a>
            <span>/</span>
            <span class="text-slate-700">Mesafeli Satış Sözleşmesi</span>
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
                <p class="mt-1 text-amber-700/90 leading-relaxed">Bu sözleşme metni Türkiye tüketici mevzuatına uyum amacıyla taslak olarak hazırlanmıştır. Sitenizin ödeme altyapısını canlıya almadan önce, iş modelinize göre hukuki geçerlilik kazanması açısından yasal danışmanınız veya bir avukat tarafından incelenerek onaylanması önemle tavsiye edilir.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 sm:p-12 prose prose-slate max-w-none">
            
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2 pb-4 border-b border-slate-100" id="mso-title">
                Mesafeli Satış Sözleşmesi
            </h1>

            <p class="text-xs text-slate-400 font-bold mb-8">Son Güncelleme: 25 Temmuz 2026</p>

            <div class="space-y-6 text-sm text-slate-600 leading-relaxed font-medium">
                
                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">1. Taraflar</h2>
                    <p>İşbu Sözleşme, aşağıdaki taraflar arasında aşağıda belirtilen hüküm ve şartlar çerçevesinde elektronik ortamda kurulmuştur:</p>
                    
                    <div class="mt-4 bg-slate-50 p-4 rounded-xl border border-slate-100 space-y-3">
                        <p><strong>SATICI (Hizmet Sağlayıcı):</strong></p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li><strong>Unvan:</strong> Wise Solutions Bilgi Teknolojileri Paz. ve Tic. Ltd. Şti.</li>
                            <li><strong>Merkez Adresi:</strong> Melikgazi / Kayseri</li>
                            <li><strong>E-posta:</strong> info@wisesolutions.com.tr</li>
                        </ul>
                        
                        <p class="mt-4"><strong>ALICI (Tüketici):</strong></p>
                        <p>Web sitesi üzerinden sipariş veren, kişisel verilerini ve fatura/teslimat adresini sipariş aşamasında beyan eden gerçek veya tüzel kişidir. Sözleşmede bundan sonra "Alıcı" olarak anılacaktır.</p>
                    </div>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">2. Sözleşmenin Konusu</h2>
                    <p>İşbu Sözleşme'nin konusu; Alıcı'nın, Satıcı'ya ait web sitesi üzerinden elektronik ortamda siparişini verdiği nitelikleri ve satış fiyatı belirtilen ürünlerin satışı ve teslimi ile ilgili olarak 6502 sayılı Tüketicinin Korunması Hakkında Kanun ve Mesafeli Sözleşmeler Yönetmeliği hükümleri gereğince tarafların hak ve yükümlülüklerinin saptanmasıdır.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">3. Sözleşme Konusu Ürün, Fiyat ve Ödeme Bilgileri</h2>
                    <p>Elektronik ortamda satın alınan ürünlerin cinsi, miktarı, marka/modeli, satış bedeli, ödeme şekli ve teslimat bilgileri siparişin tamamlandığı andaki detaylarla aynıdır ve Alıcı'nın e-posta adresine gönderilen faturada/sipariş özetinde gösterilir. Ürün fiyatlarına KDV dahildir. Ödemeler **iyzico Sanal POS** altyapısı kullanılarak kredi kartı veya banka kartı ile güvenli SSL bağlantısı üzerinden tahsil edilir.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">4. Genel Hükümler</h2>
                    <ul class="list-decimal pl-5 space-y-2">
                        <li>Alıcı, Satıcı'ya ait web sitesinde sözleşme konusu ürünün temel nitelikleri, satış fiyatı, ödeme şekli ve teslimata ilişkin ön bilgileri okuyup bilgi sahibi olduğunu ve elektronik ortamda gerekli teyidi verdiğini beyan eder.</li>
                        <li>Sözleşme konusu ürün, Alıcı'nın web sitesinde belirttiği teslimat adresine, yasal 30 günlük süreyi aşmamak kaydıyla kargo firması aracılığıyla teslim edilir.</li>
                        <li>Ürünlerin Alıcı veya gösterdiği üçüncü kişiye teslimi sırasında kargo paketinin kontrol edilmesi, hasarlı paketlerin teslim alınmayarak kargo yetkilisine tutanak tutturulması Alıcı'nın sorumluluğundadır.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">5. Cayma Hakkı</h2>
                    <p>Alıcı; ürünün kendisine veya gösterdiği adresteki kişiye tesliminden itibaren <strong>14 (on dört) gün</strong> içinde hiçbir hukuki ve cezai sorumluluk üstlenmeksizin ve hiçbir gerekçe göstermeksizin malı reddederek sözleşmeden cayma hakkına sahiptir.</p>
                    <p class="mt-2">Cayma hakkının kullanılması için bu süre içinde Satıcı'ya e-posta veya yazılı bildirimde bulunulması ve ürünün işbu sözleşmenin 6. maddesi (Cayma Hakkı Kullanılamayacak Ürünler) çerçevesinde kullanılmamış, hasar görmemiş ve orijinal ambalajının açılmamış olması şarttır.</p>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">6. Cayma Hakkı Kullanılamayacak Ürünler</h2>
                    <p>Aşağıdaki durumlarda cayma hakkı ve iade kabul edilmemektedir:</p>
                    <ul class="list-disc pl-5 mt-2 space-y-2">
                        <li><strong>Sağlık & Hijyen Ürünleri:</strong> Tesliminden sonra ambalaj, bant, mühür, paket gibi koruyucu unsurları açılmış olan ve iadesi sağlık ve hijyen açısından uygun olmayan ürünler (Sağlık & Lens bölümünden satılan Scleral Plunger aparatları, lens kapları ve vakumlu vantuzlar ambalajı açıldıktan sonra hijyen kuralları gereği **kesinlikle iade edilemez**).</li>
                        <li><strong>Elektronik Bileşenler:</strong> Ambalajı veya koruyucu paketi açılmış, statik elektrik (ESD) deşarjına maruz kalmış, lehimleme veya fiziksel müdahale yapılmış, kısa devreye maruz bırakılmış veya enerji verilmiş geliştirme kartları (ESP32, ESP8266, regülatörler, şarj modülleri vb.) iade kapsamı dışındadır.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-base font-bold text-slate-900 mb-2">7. Uyuşmazlıkların Çözümü</h2>
                    <p>İşbu Sözleşme'nin uygulanmasında, Tüketici Hakem Heyetleri ile Satıcı'nın yerleşim yerindeki (Kayseri) Tüketici Mahkemeleri yetkilidir. Siparişin onaylanması durumunda Alıcı işbu sözleşmenin tüm koşullarını kabul etmiş sayılır.</p>
                </section>

            </div>

        </div>
    </div>

@endsection
