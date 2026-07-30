# Yapılacaklar

Aklına gelen işleri buraya yaz. Yapıldıkça **Bitenler** bölümüne taşı.

Sıralama önem derecesine göre: üsttekiler önce yapılmalı.

## Önerilen sıra

| Sıra | İş | Kim | Süre |
|---|---|---|---|
| 1 | Kategori açıklamaları (madde 5) | **Mustafa** | Yarım saat |
| 2 | Ürün SSS (madde 7) | Claude + Mustafa | Kısa |
| 3 | İçerik yazımı (madde 8) | **Mustafa** | Sürekli |

Kalan işlerin ikisi sende: **altyapı hazır, içerik yok.** Blog panelden
yazılabiliyor (Panel → Blog Yazıları), kategori açıklama alanı zaten vardı.

---

## 📝 İçerik ve SEO

Ürün sayısı az. Bunu kısa vadede çözemeyiz ama **içerikle trafik çekmek** ürün
eklemekten daha hızlı sonuç verir.

### 5. Kategori açıklamaları (EN HIZLI KAZANÇ)

**Kod yazılmasına gerek yok — alan panelde zaten var.**

8 kategorinin açıklaması ya boş ya tek cümle. Her birine **150–200 kelimelik
gerçek metin** yaz. Kategori sayfalarının arama sıralamasını doğrudan etkiler.

**Kim:** Mustafa · **Süre:** Yarım saat · **Nerede:** Panel → Kategoriler

---

### 7. Ürün SSS alanı + FAQ şeması

Her ürüne 3–5 soru-cevap. Google bunları zengin sonuç olarak gösterebiliyor —
arama sayfasında rakiplerden daha çok yer kaplarsın.

**Kim:** Claude (altyapı) + Mustafa (sorular) · **Süre:** Kısa

---

### 8. İçeriğin kendisi

**Altyapı hazır olsa da metni kimse senin kadar iyi yazamaz.**

#### En güçlü kozun: DMV yetkili satıcılığı

Rakipsiz olduğun yer burası. Türkiye'de skleral lens kullananlar bilgi arıyor ve
karşılarına doğru düzgün Türkçe içerik çıkmıyor. Elektronikte 500 rakibin var,
burada belki 5.

- Skleral lens nasıl takılır ve çıkarılır (adım adım, görselli)
- DMV vantuz çeşitleri: hangisi hangi lens için?
- Lens saklama kutusu nasıl temizlenir, ne sıklıkla değiştirilir
- Sert lens kullanıcıları için günlük bakım rehberi

Bu içerikler hem arama trafiği getirir hem doğrudan ürününü satar — çünkü
rehberin sonunda satılan ürün sende.

> **Dikkat:** Sağlık içeriği yazarken "tedavi eder / iyileştirir" gibi ifadeler
> kullanma. Sitede zaten duran uyarıyı ("tıbbi teşhis veya tedavi aracı
> değildir") her rehberin sonuna koy.

#### Elektronikte: derinlik, genişlik değil

- **Karşılaştırma sayfaları** — "ESP32 vs ESP8266 hangisini seçmeliyim",
  "ESP32-C6 mı S3 mü?" Aranıyor ve iyi içerik az, üst sıraya çıkmak mümkün.
- **Proje rehberleri** — "ESP32 ile akıllı ev otomasyonu", "COB LED ile dolap içi
  aydınlatma". Her rehber kendi ürünlerine link verir.

**Kim:** Mustafa · **Süre:** Sürekli

---

## ✅ Kontrol edildi, zaten var

### Ürünü favoriye ekleme

**Çalışıyor.** Ürün detay sayfasında başlığın sağ üstündeki kalp düğmesi, ürün kartlarında sağ üst köşedeki kalp. Giriş yapmamış kullanıcı giriş sayfasına yönlendiriliyor. Favoriler **Hesabım → Favorilerim** altında listeleniyor.

`ProfileController::toggleFavorite()` + `favorites` tablosu.

**Geliştirilebilir (isteğe bağlı):**
- Favorilere eklenen ürün indirime girince e-posta — satış getirir. Stok
  bildirimi altyapısı (`StockNotifier` + `ProductObserver`) artık kurulu,
  aynı kalıpla `price` düşüşünü izlemek yeterli

---

## Bitenler

_(iş bitince buraya taşı, tarih yaz)_

### Duyurular çoklu, görselli ve biçimli oldu — 29.07.2026

Tek satırlık ayardan kendi bölümüne taşındı: **Panel → Duyurular.**

- **Çoklu duyuru:** birden fazla kaydedip yayına alabilirsin. Kanal seçimi
  var (her iki mağaza / yalnızca Elektronik / yalnızca Sağlık)
- **Biçimli metin:** kalın yazı, madde işareti, bağlantı. Uzunluk sınırı yok;
  uzun metin pencere içinde kaydırılır
- **Üç yerleşim:** sadece metin · görsel üstte, metin altta · **yazı görselin
  üzerinde** (okunabilirlik için otomatik karartma)
- **Buton:** kendi yazın ve adresin. Buton varken "Anladım" ikincil
  ("Şimdi değil") hâle gelir — asıl eylem artık ziyaretçiyi bir yere götüren
  butondur
- **Tür/simge seçimi:** Bilgi (mavi) · Uyarı (amber) · Kampanya (yeşil) ·
  Simge yok. Eskiden her duyuruda amber ünlem vardı; bir misyon metni için
  "uyarı" havası yanlıştı

> **Bir sayfada yalnızca BİR duyuru gösterilir** (sırası en küçük olan).
> Panelde "Durum" sütunu hangisinin gerçekten göründüğünü söyler.

Yayındaki duyuru migration'da yeni tabloya kopyalandı, kaybolmadı.

14 test. **Detay:** `GELISTIRICI-NOTLARI.md` → madde 2.6

---

### Kanal sayfasında sağ blog rafı — 29.07.2026

Kategoriler solda, **rehber yazıları sağda** — dikdörtgen kartlar, yeni yazı
üstte. O kanalın yazılarının yanında "Genel" olanlar da çıkar.

- Raf `xl` ve üstünde sağ kolon olur; daha küçük ekranda aynı liste sayfanın
  altında yatay ızgaraya döner
- Ürün ızgarası masaüstünde 4 → **3 kolona** indi. 4 kalsaydı kartlar
  145 px'e düşüyordu; şimdi ürünler daha büyük görünüyor
- Yazı yoksa raf hiç basılmaz

4 test. **Detay:** `GELISTIRICI-NOTLARI.md` → madde 10.8

---

### Yeni müşteri kaydında Telegram bildirimi — 29.07.2026

**Panel → Site Ayarları → Bildirimler** altında açılıp kapanan düğme.
Varsayılan kapalı; açtığın anda her yeni üyelikte Telegram mesajı gelir.

- Mesajda ad, e-posta, tarih ve toplam müşteri sayısı var — telefon/adres
  bilinçli olarak yok (Telegram mesajı sunucu dışına çıkan bir kayıt)
- Yönetici hesabı oluşması bildirim üretmez
- Telegram hiç yapılandırılmamışsa düğmenin altında uyarı çıkıyor;
  boşa umut vermesin
- `php artisan telegram:test` artık bu ayarın durumunu da yazıyor
- Bildirim hatası üyelik işlemini düşürmüyor

10 test. **Detay:** `GELISTIRICI-NOTLARI.md` → madde 10.5

---

### Panelde "yüklenirken hata oluştu" — 29.07.2026

Blog yazısı eklerken çıkan hata. Sebep: Filament v5'te `Set` sınıfı taşınmış,
kod eski yolu (`Filament\Forms\Set`) kullanıyordu. Hata sayfa açılışında değil
**başlığı yazıp alandan çıkınca** oluşuyor ve log'a hiçbir şey düşmüyor.

**Aynı hata ürün ve kategori formlarında da vardı** — "ad yaz, slug otomatik
dolsun" kodu üçünde de aynı yerden kopyalanmış. Üçü birden düzeltildi.

Sayfa testleri bunu yakalamıyordu (sayfa 200 dönüyor); form etkileşimini
sınayan 3 test eklendi.

**Detay:** `GELISTIRICI-NOTLARI.md` → madde 0

---

### 6. Blog / rehber altyapısı — 29.07.2026

Panelden yazı eklenebilen SEO uyumlu bölüm hazır: **Panel → Blog Yazıları**.

- `posts` tablosu: başlık, slug, özet, içerik, kapak görseli, kanal,
  yayın tarihi, taslak/yayında, meta başlık/açıklama
- `/blog` liste (kanal süzgeci + sayfalama) ve `/blog/{slug}` detay
- `Article` + `BreadcrumbList` JSON-LD, Open Graph (`og:type=article`),
  site haritasına otomatik ekleme — **yalnızca yayındakiler**
- Yazı içinden ürüne bağlantı verilebiliyor (zengin metin editörü)
- Yazı altında paylaş düğmeleri (madde 4'teki ortak parça)
- **Zamanlanmış yayın:** ileri tarih verilirse yazı o tarihe kadar
  görünmez. Yayında sayılmak `is_published` + `published_at` ikisine birden
  bağlı
- Sağlık bölümü seçilen yazının altına **tıbbi uyarı otomatik** ekleniyor
- **Menü bağlantıları:** üst menüde "Rehberler" simgesi (İletişim'in yanında),
  mobil hamburger menüde "Rehberler ve Yazılar", alt bilgide aynısı.
  Üçü de **yayında yazı yoksa gizlenir** — boş sayfaya götüren menü öğesi
  "site yarım kalmış" izlenimi veriyor

16 test. **Detay:** `GELISTIRICI-NOTLARI.md` → madde 10.8

> **Sıradaki iş sende:** altyapı hazır, içerik yok. En güçlü kozun DMV
> yetkili satıcılığı — madde 8'e bak.

---

### Giriş/çıkış bildirimi artık görünüyor — 29.07.2026

Giriş, kayıt ve çıkış `with('success', ...)` mesajı gönderiyordu ama
**hiçbiri ekranda görünmüyordu.** Sebep: üçü de portal sayfasına (`/`)
yönlendiriyor ve `landing.blade.php` `layouts.app`'i kullanmıyor — bildirim
kutusu orada hiç yoktu.

- Bildirim kutusu `partials/toast.blade.php` içine alındı, hem layout'a hem
  portal sayfasına dâhil edildi
- Alpine bağımlılığı kaldırıldı (portal sayfası Alpine yüklemiyor);
  `show-toast` olay arayüzü aynı kaldı
- Sağ **alttan sağ üste** taşındı — "Sepete Eklendi" bildirimiyle aynı köşe.
  Altta mobil gezinme çubuğunun arkasında kalıyordu
- İki bildirim üst üste binmesin diye sepet bildirimi gelince diğeri çekiliyor

7 test. **Detay:** `GELISTIRICI-NOTLARI.md` → madde 2.5

---

### 4. Ürün paylaşma kısayolları — 29.07.2026

Ürün detayında, sepete ekle satırının altında paylaş kutusu:
WhatsApp (önce, Türkiye'de baskın kanal), X, Facebook, "Bağlantıyı Kopyala".

- Mobilde (`pointer: coarse` + `navigator.share`) cihazın kendi paylaşım
  menüsü açılıyor, düğmeler gizleniyor. Masaüstünde düğmeler açık kalıyor —
  orada tek "Paylaş" düğmesi hangi kanala gideceğini gizlerdi.
- Her bağlantı `utm_source` + `utm_medium=share` taşıyor; Analytics'te kanal
  ayrımı ancak böyle görünüyor. `canonical` etiketi `url()->current()`
  kullandığı için sorgu dizisi SEO'yu bölmüyor.
- Kopyalama, `clipboard` API'si yoksa (http üzerinden yerel test) gizli
  `textarea` ile yedekleniyor.
- Open Graph etiketleri zaten vardı, ek iş çıkmadı.

**Nerede:** `resources/views/partials/share_buttons.blade.php` — blog yazıları
için de kullanılabilir. 4 test.

---

### 3. Favori düğmesi artık görünüyor — 29.07.2026

Kalp, sepete ekle satırının en sonundaydı ve yazısı yoktu; mobilde `flex-col`
yüzünden turuncu düğmenin **altına** düşüyor, görmek için kaydırmak
gerekiyordu.

- Kalp **ürün adının hizasına, sağ üst köşeye** taşındı
- Yanında "Favorilerime Ekle" / "Favorilerimde" yazısı (mobilde yalnız simge)
- Favorideyken içi dolu pembe, değilken çerçeveli gri — fark belirgin
- Tıklayınca **sayfa yenilenmiyor** (AJAX), sağ üstte kısa bir bildirim çıkıyor
- `ProfileController::toggleFavorite()` artık `expectsJson()` kullanıyor;
  `ajax()` sadece `X-Requested-With` başlığına baktığı için fetch çağrısı
  yönlendirme alıp sessizce sayfayı yeniliyordu
- Ürün kartlarındaki kalbe dokunulmadı, o zaten doğru yerde

7 test (favori özelliğinin daha önce hiç testi yoktu).

---

### 2. Vitrin ürünlerinde görsel ve başlık tıklanabilir — 29.07.2026

Anasayfa vitrin kartlarında yalnızca küçük "İncele" düğmesi bağlantıydı;
görsele ve ürün adına yapılan tıklamalar boşa gidiyordu.

Görsel `<a>` içine alındı (`aria-label` ile), başlık bağlantı oldu.
Kartın tamamı bağlantı yapılmadı — içinde sepete ekleme formu var. 2 test.

---

### Kargo rozeti gerçek koşulu gösteriyor — 29.07.2026

Ürün sayfası koşulsuz **"Kargo Bedava & Bugün Kargoda!"**, anasayfa şeridi
**"Ücretsiz Kargo / Tüm siparişlerde"** diyordu — panelde ücretsiz kargo alt
limiti tanımlıyken bile. Müşteri ödeme adımında sürpriz kargo ücretiyle
karşılaşıyordu.

Artık metin `Setting::shippingNotice()`'ten geliyor:
- Kargo ücreti 0 → "Ücretsiz Kargo · Tüm siparişlerde"
- Eşik tanımlı, ürün fiyatı eşiğin altında → "Ücretsiz Kargo · 500,00 TL ve üzeri siparişlerde"
- Eşik tanımlı, ürün fiyatı eşiği geçiyor → "Ücretsiz Kargo · Bu ürün için geçerli"
- Kampanya yok → "Standart Kargo · 49,90 TL" (ücreti gizlemek yerine yazıyoruz)

6 test; biri rozetin `shippingCostFor()` ile aynı sonucu vermesini garanti
ediyor, ikisi birbirinden kopamaz.

**Detay:** `GELISTIRICI-NOTLARI.md` → madde 9

---

### 1. "Stok Gelince Haber Ver" artık gerçekten çalışıyor — 29.07.2026

Buton sadece bir açılır pencere gösteriyor, hiçbir yere kaydetmiyordu.

Yapılanlar:
- `stock_notifications` tablosu — ürün, e-posta, kullanıcı, `notified_at`
- Üç düğme de (detay, ürün kartı, vitrin) gerçek POST atıyor; e-posta üye
  girişliyse hesaptan alınıyor, misafirse soruluyor
- Aynı e-posta + ürün için ikinci kayıt açılmıyor; ürün gelip tekrar
  tükenirse aynı kişi ikinci turda da haber alıyor
- Stok 0'dan yukarı çıktığı anda bekleyenlere e-posta gidiyor
  (`ProductObserver` → `StockNotifier`)
- Panel → Ürünler'de **"Stok Bekleyen"** sütunu + filtresi; sıralanabilir,
  hangi ürünü önce tedarik edeceğini söyler
- KVKK aydınlatma metnine e-posta toplama maddesi eklendi
- 11 test

**Detay:** `GELISTIRICI-NOTLARI.md` → madde 10.6
