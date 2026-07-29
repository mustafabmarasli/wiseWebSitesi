# Yapılacaklar

Aklına gelen işleri buraya yaz. Yapıldıkça **Bitenler** bölümüne taşı.

Sıralama önem derecesine göre: üsttekiler önce yapılmalı.

## Önerilen sıra

| Sıra | İş | Kim | Süre |
|---|---|---|---|
| 1 | Stok bildirimi gerçekten çalışsın (madde 1) | Claude | Orta |
| 2 | Vitrin tıklama + favori görünürlüğü (madde 2, 3) | Claude | Küçük |
| 3 | Kategori açıklamaları (madde 5) | **Mustafa** | Yarım saat |
| 4 | Blog altyapısı (madde 6) | Claude | Yarım gün |
| 5 | Paylaşım düğmeleri (madde 4) | Claude | Küçük |
| 6 | Ürün SSS (madde 7) | Claude + Mustafa | Kısa |
| 7 | İçerik yazımı (madde 8) | **Mustafa** | Sürekli |

3. sıradaki iş kod gerektirmiyor — Claude başka bir şey yaparken paralel
ilerleyebilirsin.

---

## 🔴 Önce bunlar — müşteriye yanlış söz veriyoruz

### 1. "Stok Gelince Haber Ver" hiçbir şey kaydetmiyor

**Durum:** Buton var, müşteriye *"E-posta adresiniz kaydedildi, ürün stoklarımıza girdiğinde bilgilendirme gönderilecektir"* diyor — ama **hiçbir yere kaydetmiyor ve hiçbir zaman e-posta göndermiyor.**

Sadece bir açılır pencere gösteriyor. Sunucuya istek gitmiyor, veritabanında tablo yok, gönderim yok.

**Nerede:** `resources/views/layouts/app.blade.php` → `notifyStock()` fonksiyonu.
Buton üç yerde: ürün detay sayfası, ürün kartları, anasayfa vitrin kartları.

**Neden acil:** Stokta olmayan ürüne ilgi duyan müşteri en değerli müşteri. Şu an onlara yalan söylüyoruz; bekleyip haber gelmeyince bir daha gelmezler.

**Yapılacak:**
- `stock_notifications` tablosu (ürün, e-posta, kullanıcı, bildirildi mi, tarih)
- Butondan gerçek POST — aynı e-posta + ürün için tekrar kayıt açılmasın
- Panelden stok 0'dan yukarı çıkınca bekleyenlere e-posta
- Panelde "kaç kişi bekliyor" göstergesi — hangi ürünü tedarik edeceğini bu söyler
- KVKK: e-posta toplanıyor, aydınlatma metnine eklenmeli

**Ara çözüm (5 dakika):** Düzeltene kadar butonun metnini dürüst yap veya butonu gizle.

---

## 🟠 Hata

### 2. Vitrin ürünlerinde görsele/başlığa tıklanınca ürün açılmıyor

**Durum:** Anasayfadaki iki büyük vitrin kartında **sadece küçük "İncele" düğmesi** bağlantı. Ürün görseline veya adına tıklamak hiçbir şey yapmıyor.

Herkes içgüdüsel olarak görsele tıklar. Şu an o tıklamalar boşa gidiyor.

**Nerede:** `resources/views/home.blade.php` → "VİTRİN ÜRÜNLER" bölümü (görsel `<div>`'i ve `<h3>` bağlantı değil).

**Yapılacak:** Görseli ve başlığı `<a href="{{ route('product.detail', $feat->slug) }}">` içine al.

> Kartın **tamamını** bağlantı yapma — içinde sepete ekleme formu var, bağlantı içine buton/form koymak geçersiz HTML.

**Zorluk:** Küçük. Yarım saat.

---

### 3. Favori düğmesi ürün sayfasında fark edilmiyor

**Durum:** Favori özelliği çalışıyor ama düğme görünmüyor denecek kadar siliktir.

Ürün detay sayfasında kalp, satırın **en sonunda**: önce adet seçici, sonra koca turuncu "Sepete Ekle", en sağda gri çerçeveli içi boş bir kalp. Yazısı yok.

Mobilde daha kötü: satır `flex-col` olduğu için kalp, sepete ekle düğmesinin **altına** düşüyor — ekranda görmek için aşağı kaydırmak gerekiyor.

**Nerede:** `resources/views/detail.blade.php` → "Buy / Add to Cart Form" bölümü, `<!-- Favorite button -->`.

**Yapılacak — önerilen çözüm:** Kalbi bu satırdan çıkar, **ürün adının hizasına, sağ üst köşeye** taşı. Trendyol, Hepsiburada ve Amazon hep orada tutuyor; müşteri oraya bakmaya alışkın.

- Ürün başlığı ile aynı satır, sağa yaslı
- İçi dolu/boş kalp farkı belirgin olsun (favorideyken dolu ve pembe, değilken çerçeveli)
- Yanına küçük "Favorilerime Ekle" yazısı — sadece simge ne demek olduğunu anlatmıyor
- Tıklayınca sayfa yenilenmesin (AJAX) — sayfa başa dönünce kullanıcı nerede kaldığını kaybediyor

**Alternatif (daha az iş):** Yerinde kalsın ama pembe zemin + "Favorilerime Ekle" yazısı eklensin, mobilde sepete ekle düğmesinin **üstüne** alınsın.

> Ürün **kartlarındaki** kalp (görselin sağ üstü) zaten doğru yerde, ona dokunma.

**Zorluk:** Küçük. AJAX'sız yarım saat, AJAX'lı 1–2 saat.

---

## 🟢 Yeni özellik

### 4. Ürün paylaşma kısayolları (WhatsApp + sosyal medya)

**Durum:** Yok.

**Yapılacak:** Ürün detay sayfasına paylaş düğmeleri:
- **WhatsApp** — Türkiye'de en çok kullanılan, önceliği bu
- X, Facebook, "Bağlantıyı kopyala"
- Mobilde cihazın kendi paylaşım menüsü (`navigator.share`) — destekliyorsa onu kullan, desteklemiyorsa düğmeleri göster

**Notlar:**
- Paylaşılan bağlantının WhatsApp'ta görsel + başlıkla görünmesi için Open Graph etiketleri gerekli — **bunlar zaten var**, ek iş yok
- Paylaşım linklerine `?utm_source=whatsapp` ekle; Analytics'te hangi kanaldan geldiğini görürsün

**Zorluk:** Küçük–orta. Yarım gün.

---

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

### 6. Blog / rehber altyapısı

Panelden yazı eklenebilen, SEO uyumlu bir bölüm.

- `posts` tablosu: başlık, slug, özet, içerik, kapak görseli, kanal, yayın tarihi, taslak/yayında
- `/blog` liste + `/blog/{slug}` detay sayfası
- `Article` JSON-LD şeması, Open Graph, sitemap'e otomatik ekleme
- Yazı içinden ürüne link verebilme — **asıl para bu bağlantıda**

**Kim:** Claude · **Süre:** Yarım gün

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

**Çalışıyor.** Ürün detay sayfasında fiyatın yanındaki kalp düğmesi, ürün kartlarında sağ üst köşedeki kalp. Giriş yapmamış kullanıcı giriş sayfasına yönlendiriliyor. Favoriler **Hesabım → Favorilerim** altında listeleniyor.

`ProfileController::toggleFavorite()` + `favorites` tablosu.

**Ama görünürlüğü sorunlu** → 3 numaralı maddeye bak.

**Geliştirilebilir (isteğe bağlı):**
- Favorilere eklenen ürün indirime girince e-posta — satış getirir, ama önce 1 numaradaki bildirim altyapısı kurulmalı, ikisi aynı işi yapar

---

## Bitenler

_(iş bitince buraya taşı, tarih yaz)_
