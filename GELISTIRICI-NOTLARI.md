# Geliştirici Notları

Bu projede kod yazarken **kaybedilmiş saatler** pahasına öğrenilmiş şeyler.
Yeni bir özelliğe başlamadan önce ilgili bölüme göz at.

Günlük işletme komutları (SSH, deploy, yayınlama) için → [`NOTLAR.md`](NOTLAR.md)

---

## 0. Filament v5: `Set` ve `Get` sınıfları taşındı

```php
use Filament\Schemas\Components\Utilities\Set;   // DOĞRU
use Filament\Forms\Set;                          // YANLIŞ — v4 yolu
```

Eski yolu kullanan bir closure **açılışta değil, alan güncellenince** patlar:
`TypeError: Argument #1 ($set) must be of type Filament\Forms\Set,
Filament\Schemas\Components\Utilities\Set given`.

Panelde görünen tek şey **"yüklenirken hata oluştu"**; `laravel.log`'a hiçbir
şey düşmez, sayfa testleri de yakalamaz (sayfa 200 döner). Yakalamanın tek
yolu form etkileşimini sınamaktır:

```php
Livewire::actingAs($admin)->test(CreatePost::class)
    ->fillForm(['title' => 'Deneme'])
    ->assertFormSet(['slug' => 'deneme']);
```

> **Geçmiş hata:** Ürün, kategori ve blog formlarının üçünde birden vardı —
> "başlık yaz, slug otomatik dolsun" kodu her üçünde de eski yolu
> kullanıyordu. `BlogPanelTest` üç formu birlikte kolluyor.

Yeni bir form yazarken slug/otomatik doldurma kodunu **çalışır bir formdan
kopyalamadan önce** import satırına bak.

---

## 1. Filament panel: Tailwind sınıfları çalışmaz

**Belirti:** Panelde yazdığın `hidden`, `h-10`, `grid-cols-3` gibi sınıflar hiçbir
etki etmez. Gizlemek istediğin dosya seçici görünür, ikon devasa çıkar.

**Sebep:** Site ön yüzü Tailwind'i CDN'den yükler ama **admin paneli Filament'in
kendi derlenmiş CSS'ini kullanır.** O CSS yalnızca Filament'in kendi kullandığı
sınıfları içerir; senin yazdığın rastgele yardımcı sınıflar orada yoktur.

**Çözüm:** Panel sayfalarında kendi stilini yaz. Örnek:
[`resources/views/filament/pages/bulk-image-upload.blade.php`](resources/views/filament/pages/bulk-image-upload.blade.php)
— sayfaya özel `<style>` bloğu ve `tg-` önekli sınıflar.

Ön yüz (`resources/views/*.blade.php`) için bu geçerli değil, orada Tailwind serbest.

---

## 1.1. Ürün listesinde satır içi düzenleme

Panel → Ürünler tablosunda **fiyat, eski fiyat ve stok** doğrudan listeden
düzenlenir (`TextInputColumn`). Her ürün için forma girip çıkmak, fiyat/stok
gibi sık yapılan bir işte gereksiz yol.

- Satır içi kayıt da **model olayı üretir**: stoğu listeden 0'dan yukarı
  çekmek bekleyenlere e-posta gönderir (madde 10.6). Kasıtlı; testle sabit.
- `eski_fiyat` boş bırakılabilmeli. Boş dize `null`'a çevrilmezse `0,00`
  kaydediliyor ve vitrinde **"%100 indirim"** çıkıyor — bu yüzden o sütunda
  `updateStateUsing` var.
- Doğrulama kuralları sütunda tanımlı (`rules([...])`); negatif stok/fiyat
  kaydedilmez.

### Üründe hangi alanlar zorunlu, neden?

| Alan | Zorunlu mu | Sebep |
|---|---|---|
| Kategori | Evet | Ürünün hangi mağazada görüneceğini kategori belirler; detay sayfası kategori adını yazıyor, kategorisiz ürün hiçbir listede çıkmaz |
| Ürün adı | Evet | — |
| Adres (slug) | Evet | Ürünün URL'i. **Yayına aldıktan sonra değiştirme** |
| Fiyat | Evet | Sepet ve sipariş toplamı bu alandan hesaplanıyor |
| Stok | Evet (varsayılan 0) | Stok karşılaştırmaları sayı bekliyor; `null` kalırsa sepet ve stok düşümü kırılır |
| Satış sayısı | **Hayır** | Sayaç ama sistem hiçbir yerde artırmıyor, elle giriliyor. Zorunluluğu kaldırıldı, boş bırakılırsa 0 |

---

## 1.2. Panel marka bağlantısı `APP_URL`'e gider

Panelde sol üstteki **"Buy WISEly"** yazısı panel anasayfasına değil **sitenin
kendisine** gider (`AdminPanelProvider` → `->homeUrl(config('app.url'))`).

Adres `.env` içindeki `APP_URL`'den okunur, koda gömülmedi: sabit yazılsaydı
yerelde çalışırken de canlı siteye atlardı. Bu yüzden **canlıda `APP_URL`
doğru olmalı** — zaten olmak zorunda, Google giriş yönlendirmesi de
(`GOOGLE_REDIRECT_URI="${APP_URL}/..."`) aynı değişkene bağlı.

---

## 1.3. Ürün paneli: filtreler ve sütun seçimi

Panel → Ürünler tablosunda filtreler genişletildi:

- **Kategori** — ilişki üzerinden, aranabilir
- **Marka** — seçenekler veritabanındaki gerçek markalardan üretilir
  (`Product::distinct()->pluck('brand')`); yeni marka eklenince filtreye
  elle eklemek gerekmez
- **Stok Durumu** — Tükendi (0) / Az Stok (1-9) / Stokta (10+)
- **Vitrin** — üçlü filtre (evet/hayır/farketmez)
- **İndirimli** — yalnızca `eski_fiyat > price` olanlar
- **Fiyat Aralığı** — iki alanlı özel filtre (`Filter::make()->schema([...])`),
  aktifken üstte "Min: X ₺ / Max: Y ₺" göstergesi çıkar (`indicateUsing`)

**Sütun seçimi** ("Kolonlar" düğmesi) daha zengin: marka, barkod, slug, puan,
eklenme/güncelleme tarihi, satış ve görüntülenme sayısı artık
`toggleable(isToggledHiddenByDefault: true)` — varsayılan görünümü sade
tutar, gerektiğinde açılabilir. Marka ve barkod sütunları aranabilir.

---

## 1.4. Kategoriye özel arama kutusu

Kategori sayfasının başlığının altında, o kategoriyle **sınırlı** bir arama
kutusu var (`category-search-input`). Üstteki genel arama tüm kanalı
tarıyor; ziyaretçi zaten bir kategorinin içindeyse başa dönüp genel aramayı
kullanmak zorunda kalmasın diye ayrı bir kutu eklendi.

- `ProductController::category()` artık `q` parametresini kabul ediyor,
  yalnızca o kategorinin ürünlerinde arıyor
- Diğer filtrelerle (fiyat aralığı, sıralama) birlikte çalışır — form
  `request()->except(['q','page'])` ile diğer parametreleri gizli alan
  olarak taşır
- Sonuç sayısı ve "aramayı temizle" bağlantısı başlığın altında çıkar
- Aynı görünüm (`category.blade.php`) genel arama sayfasında da
  kullanıldığı için (`ProductController::search()`), oradaki `q` kutusuyla
  çakışmaz — farklı `<form>` etiketleri, farklı `id`ler

---

## 1.5. Sayfa genişliği: `max-w-site`

Gövde genişliği **tek yerden** yönetilir: `layouts/app.blade.php` içindeki
Tailwind yapılandırmasında `maxWidth.site` (şu an **1680px**).

**Görünümlerde `max-w-7xl` YAZMA**, `max-w-site` kullan. Aksi hâlde genişliği
değiştirmek 29 yeri tek tek düzenlemek demek.

Metin ağırlıklı sayfalar kendi dar kabını kullanmaya devam eder ve buna
dokunulmamalı — uzun satır okumayı zorlaştırır:

| Sayfa | Kap |
|---|---|
| Mağaza, kategori, sepet, detay, blog listesi | `max-w-site` |
| Yasal metinler | `max-w-4xl` (içerik) |
| Blog yazısı | `max-w-3xl` |

Genişliği değiştirirsen **ızgara kolon sayılarını da gözden geçir**:

- Anasayfa ürün ızgarası `lg:grid-cols-3 2xl:grid-cols-4`. 4. kolon `xl`de
  değil `2xl`de açılıyor: sağ blog rafıyla birlikte `xl` (1280px) genişlikte
  kartlar 146px'e düşüyordu.
- Kategori sayfasında sağ raf yok, orada 4. kolon `xl`de açılır.
- Ürün detayında satın alma satırı `xl:max-w-2xl` ile sınırlı. Bu sınır
  olmadan "Sepete Ekle" düğmesi 884px'e uzayıp çirkin duruyordu.

> **Geçmiş hata:** Kap 1280px'ken 2560px ekranda iki yanda 640px boşluk
> kalıyordu ve ürünler 3 kolona sıkışmış görünüyordu.

---

## 2. Modal'lar `@section('modals')` içine konur

**Belirti:** `position: fixed` bir pencere ekranın ortasında değil, sayfanın çok
aşağısında beliriyor. Yükseklik viewport yerine 5000px+ çıkıyor.

**Sebep:** `layouts/app.blade.php` içindeki `<main class="flex-grow animate-fade-in">`
bir CSS animasyonu çalıştırıyor ve bu animasyon `transform` üretiyor.
**`transform` içeren bir öğe, içindeki `position:fixed` öğeler için yeni bir
referans çerçevesi oluşturur** — artık viewport'a değil o öğeye göre hizalanırlar.

**Çözüm:** Tam ekran katman gerektiren her şeyi `@section('content')` yerine
`@section('modals')` içine koy. Layout bunu `<main>` dışında, `<body>` seviyesinde
basar.

```blade
@section('modals')
    @include('partials.announcement')
@endsection
```

Aynı tuzak `filter`, `perspective`, `will-change`, `backdrop-filter` için de geçerli.

---

## 2.6. Duyurular `settings` içinde DEĞİL

Duyurular `announcements` tablosundadır → Panel → **Duyurular**.
Eskiden `settings` satırında tek başlık + tek düz metin olarak duruyordu;
görsel, biçimli metin ve kanal ayrımı sığmadı.

- **Çoklu duyuru SIRAYLA gösterilir:** kanaldaki tüm yayında duyurular
  sayfaya basılır (`Announcement::queueForChannel()`), Alpine yalnızca sırası
  geleni gösterir. Ziyaretçi birincisini kapatınca 220 ms sonra ikincisi
  açılır. Hepsini aynı anda basmak üst üste binen pencereler demek olurdu.
- Kapatılanlar `sessionStorage`'da **liste** olarak tutulur
  (`duyuru_kapatilanlar`). Tek anahtar kullanılırsa ikinci duyuru
  kapatıldığında birincisi "kapatılmamış" sayılıp geri geliyordu.
- Birden fazla duyuru varken kartta **"1 / 2 duyuru"** sayacı ve kapatma
  düğmesinde **"Sıradaki duyuru →"** yazar; kapatınca yeni pencere açılması
  sürpriz olmasın.
- Panelde "Durum" sütunu kaydın kaçıncı sırada açıldığını söyler.
- `channel` = `both` her iki mağazada geçerli.
- Gövde zengin metin editöründen **HTML** gelir, `{!! !!}` ile basılır.
  Yalnızca yönetici yazabildiği için güvenli.
- **Renkler panelden seçilir** (`bg_color`, `text_color`). İkisi de boş
  bırakılabilir; o zaman otomatik: zemin koyuysa beyaz yazı, açıksa koyu yazı
  (`isDarkBackground()` — ITU-R BT.601 parlaklık hesabı).
- Yerleşim `image_overlay` seçildiğinde `bg_color` görselin üzerine serilen
  **perdenin** rengidir. Perde şart: açık renkli görselde yazı okunmuyor.
- Kart üzerindeki `color`, **sayaç, kapatma yazısı ve zengin metnin tamamı
  tarafından miras alınır.** Kalın yazı ve bağlantılar da `inherit` kullanır —
  sabit koyu renkler koyu zeminde kayboluyordu. Bağlantının ayırt edilmesi
  kalınlık ve alt çizgiden geliyor.
- Dört yerleşim var: sadece metin · görsel üstte · **metin üstte görsel altta**
  · yazı görselin üzerinde.
- Kart `max-height:90vh` + kaydırmalıdır — uzun bir misyon metninde buton
  ekranın dışına çıkıyordu.
- Ton (`info`/`warning`/`campaign`/`none`) simge ve rengi belirler. Renkler
  Blade'de tam değer olarak yazılır; duyuru penceresi Tailwind değil gömülü
  CSS kullanıyor.

> **Göç notu:** `2026_07_29_170000` migration'ı `settings` içindeki mevcut
> duyuruyu yeni tabloya **kopyalar**, sonra o kolonları düşürür. Yayındaki
> duyuru kaybolmasın diye. Geri alma (`down`) kolonları boş olarak geri ekler;
> veri yeni tabloda kalır.

---

## 3. Konum verisi migration'da değil

`provinces` (81), `districts` (973), `neighborhoods` (74.402) tablolarının
**şeması** migration'da, **verisi** kök dizindeki SQL dökümlerinde.

Sıfırdan kurulum ya da `migrate:fresh` sonrası:

```bash
php artisan migrate
php artisan locations:import
```

**Bunu atlarsan ödeme adımı tamamen kırılır** — checkout `exists:neighborhoods,id`
doğrulaması yapıyor.

> **Geçmiş hata:** `locations:import` eskiden 6.8 MB'lık dosyayı tek SQL paketi
> olarak gönderiyordu, MySQL `max_allowed_packet` ile reddediyordu. Daha kötüsü,
> hatayı yutup "başarıyla içe aktarıldı" diyordu. Şimdi ifadeleri tek tek
> çalıştırıyor ve hata varsa `FAILURE` dönüyor. Tabloları sıfırlamak için `--fresh`.

---

## 4. Fiyat: `price` mi `active_price` mi?

Şu an **sepet ve sipariş hesabı `price` üzerinden** yürüyor.
`active_price` accessor'ı `discount_price` varsa onu döndürür ama
**hesaplamalarda kullanılmıyor.**

Birine geçeceksen ikisini birlikte değiştir:
- `CartController::syncCart()` — sepet satırını kuran yer
- Ürün kartları / detay sayfası — gösterim

Aksi halde **vitrinde görünen fiyatla karttan çekilen tutar birbirini tutmaz.**

---

## 5. Test veritabanı SQLite

`phpunit.xml` testleri bellekte SQLite ile çalıştırır. Sonuçları:

- **MySQL'e özgü ham SQL yazma.** Yazacaksan driver kontrolü koy:
  ```php
  if (DB::getDriverName() === 'mysql') { DB::statement('ALTER TABLE ... MODIFY ...'); }
  ```
  (Örnek: `2026_07_25_194036_update_orders_table...`)
- **`ENUM` kolon kullanma**, `string` + uygulama katmanında enum kullan (bkz. madde 6).
- Testlerde `static` değişkenle model önbelleğe alma — `RefreshDatabase` her testte
  veritabanını geri alır, önbellekteki ID bir sonraki testte geçersiz kalır.

Testler **canlı/yerel MySQL'e dokunmaz**, gönül rahatlığıyla çalıştır.

---

## 6. Sipariş durumları: `App\Enums\OrderStatus`

Durum metinleri ve renkleri **tek kaynakta**. Elle `'paid'`, `'Ödendi / Hazırlanıyor'`
yazma:

```php
OrderStatus::paidStatuses()      // ['paid','shipped','delivered'] — ciroya sayılanlar
OrderStatus::options()           // Filament select/filter için
OrderStatus::labelFor($deger)    // güvenli metin (bilinmeyen değerde ham değeri döner)
OrderStatus::colorFor($deger)    // rozet rengi
```

Yeni durum eklerken yalnızca enum'a ekle. Veritabanı kolonu `VARCHAR`, migration
gerekmez.

---

## 7. Görseller

**Yüklenen görseller git'e girmez** (`storage/app/public/*` yoksayılıyor). Doğrusu
budur — medya deposu şişirir.

- Yerelde yüklediğin görsel **canlıda görünmez**, tersi de geçerli
- Canlı görseller **canlı panelden** yüklenmeli
- `public/storage` sembolik bağı gerekli. Hostinger'da `php artisan storage:link`
  **çalışmaz** (`exec()` kapalı), elle kur:
  ```bash
  ln -s ~/domains/wisesolutions.com.tr/public_html/storage/app/public \
        ~/domains/wisesolutions.com.tr/public_html/public/storage
  ```

Veritabanında **iki format bir arada**: `img/...` (seeder, `public/` altında) ve
`products/...` (panel yüklemesi, public disk). İkisini de
`ResolvesImagePaths` trait'i çözer — view'de `$product->image_url` kullan,
asla `asset($product->image_path)` yazma.

**Toplu yükleme:** Panel → Toplu Görsel Yükle. Dosya adı ürünün slug'ıyla eşleşir,
görseller **tarayıcıda** WebP'ye çevrilip 1200px'e küçültülür (sunucuda GD/Imagick
gerekmez).

---

## 8. Ödeme akışında dokunma

### İki ödeme yolu var, ikisi de aynı yerde biter

| Yol | Nasıl başlar | Sipariş durumu | Ödendi'ye kim geçirir |
|---|---|---|---|
| Havale / EFT | `CartController::initiatePayment()` → banka sayfası | `pending` | Yönetici, panelden **"Ödeme Geldi, Onayla"** |
| Kredi kartı | iyzico Checkout Form | `pending` | `PaymentController::callback()` |

**Stok düşümü ve kupon sayacı yalnızca `OrderFulfiller::markPaid()` içinde işlenir.**
Bu mantığı ikinci bir yere kopyalama — iki yol ayrı ayrı yazılsaydı biri
güncellenip diğeri unutulduğunda stok tutmazdı.

`markPaid()` idempotenttir: sipariş `pending` değilse hiçbir şey yapmaz ve
`false` döner. Bu erken çıkışı kaldırma.

**Havale siparişinde stok, ödeme onaylanana kadar DÜŞMEZ.** Bilerek böyle:
parası hiç gelmeyecek siparişler yüzünden gerçekte satılabilir ürünü rafta
yokmuş gibi göstermemek için.

### Ödeme yöntemi açık mı kontrolü

`initiatePayment()`, alan doğrulamasından **önce** yöntemin panelde açık
olduğunu denetler. İki sebeple bu sırada:

1. Kapalı bir yöntem forma müdahale edilerek POST edilemesin
2. TC Kimlik No'nun zorunlu olup olmadığı ödeme yöntemine ve tutara bağlı —
   doğrulama kuralı kurulabilmesi için önce bunların bilinmesi gerekiyor

`Setting::offersBankTransfer()` IBAN ve hesap adı doluysa `true` döner.
**İkisi de boşken ödeme sayfası bilerek kapalıdır** — müşteriye parayı nereye
göndereceğini söyleyemiyorsak sipariş almamalıyız.

### `PaymentController::callback()` içinde bilinçli olarak duran korumalar:

| Koruma | Ne için |
|---|---|
| `status !== 'pending'` erken çıkış | Callback tekrar gelirse stok/kupon/e-posta ikinci kez işlenmesin |
| `paidPrice` ↔ `total_amount` karşılaştırması | Tutar manipülasyonu; uyuşmazlıkta sipariş `review` durumuna düşer |
| Koşullu `decrement` (`where stock >=`) | Eşzamanlı satışta stok negatife düşmesin |
| Kuponda `lockForUpdate` | İki eşzamanlı ödeme limiti aşmasın |

Sipariş sonuç sayfaları (`/siparis/basarili/{id}`) **imzalı bağlantı, oturum kanıtı
veya sahiplik** ister. Bu kontrolü gevşetme — eskiden herkes ID değiştirerek
başkasının adresini/telefonunu görebiliyordu.

---

## 8.5. Sipariş numarası: `id` değil `order_number`

Müşteriye gösterilen numara `260729-WISE-K4M2` biçimindedir (tarih + rastgele sonek).

- **`id` birincil anahtar olarak yerinde durur** — yabancı anahtarlar, rotalar ve
  daha önce gönderilmiş imzalı bağlantılar ona bağlı. Rota anahtarını değiştirme.
- **Görünen her yerde `$order->display_number` kullan** — e-posta, havale sayfası,
  panel, Excel. `$order->id` yazma.
- Sonekte `0/O` ve `1/I/L` yok: numara telefonda okunuyor ve havale açıklamasına
  elle yazılıyor.
- Saat kullanılmadı çünkü aynı saniyedeki iki sipariş çakışırdı. Benzersizlik
  hem `unique` indeksle hem üretim döngüsündeki kontrolle garanti altında.

---

## 8.6. TC Kimlik No koşullu zorunlu

Nihai tüketiciye kesilen faturada TC Kimlik No **zorunlu değildir**; yalnızca
tutar fatura düzenleme haddini aşarsa gerekir. Gerekmiyorken toplamak KVKK'nın
veri minimizasyonu ilkesine aykırı ve ödeme adımında terk sebebi.

`Setting::requiresIdentityNumber()` tek karar noktasıdır. `true` döndüğü haller:

- Ticari fatura seçilmiş
- Kartla ödeme (iyzico `buyer.identityNumber` alanını zorunlu tutuyor)
- Tutar `identity_required_threshold` değerine eşit/üstünde (panelden yönetilir)

Eşik **koda gömülmemeli** — her yıl yeniden değerleme ile değişiyor
(2025: 9.900 ₺, 2026: 12.000 ₺).

Zorunlu olmaması boş geçilebilmesi demek; **girilirse yine de TC algoritmasından
geçer.** Bu doğrulamayı kaldırma.

---

## 9. Ayarlar veritabanında, kodda değil

Kargo ücreti, ücretsiz kargo limiti, **banka/havale bilgileri**, **ödeme
yöntemi açık/kapalı**, **TC eşiği** ve **yeni müşteri Telegram bildirimi**
`settings` tablosunda tek satırda tutulur → Panel → **Site Ayarları**.

> Duyurular buradan çıktı, kendi tablosuna taşındı → madde 2.6.

Yani bunları değiştirmek için deploy gerekmez. Ama tersi de doğru:
**kodu canlıya atmak ayarı açmaz** — canlı panelden ayrıca açman gerekir.

`Setting::current()` her zaman bir satır döndürür (yoksa oluşturur).

### Vitrinde "kargo bedava" yazma

Kargo koşulu **tek yerden** gelir: `Setting::shippingNotice()`. Ürün sayfası ve
anasayfa şeridi bu metodu kullanır (`partials/shipping_notice.blade.php`).

Görünen metin ile tahsil edilen tutar ayrışırsa müşteri ödeme adımında sürpriz
ücretle karşılaşır — en pahalı sepet terk sebebi budur. `ShippingTest` içindeki
"kargo rozeti ayarla sepet hesabıyla aynı sonucu verir" testi bu ikisinin
birbirinden kopmasını engeller: rozet "ücretsiz" diyorsa `shippingCostFor()`
de 0 dönmek zorunda.

> **Geçmiş hata:** Ürün sayfasında koşulsuz "Kargo Bedava & Bugün Kargoda!",
> anasayfada "Ücretsiz Kargo / Tüm siparişlerde" yazıyordu — eşik panelde
> tanımlıyken bile. Google Merchant beslemesi (`MerchantFeedController`) ise
> gerçek ücreti bildiriyordu; yani Google'a doğru, müşteriye yanlış söyleniyordu.

---

## 10. KVKK / gizlilik

- **TC Kimlik No şifreli saklanır** (`'identity_number' => 'encrypted'` cast).
  **`APP_KEY` değişirse tüm kayıtlı kimlik numaraları okunamaz hâle gelir.**
  Yedekle, asla değiştirme.
- Görüntülenme kaydında IP ham saklanmaz, `sha256` ile hash'lenir.
- Sipariş dışa aktarımı (CSV) kişisel veri içerir — dosyayı paylaşırken dikkat.
- `UserExporter` parola hash'i ve `remember_token` içermez; **eklemeyin.**

---

## 10.4. Pazarlama izni KVKK izninden AYRIDIR

**En kritik ayrım:** "Aydınlatma metnini okudum" kutusu (`kvkk_consent`)
pazarlama iletisi göndermeye izin VERMEZ.

| | Kapsamı | Nerede |
|---|---|---|
| KVKK onayı | Verinin **işlenmesi** | Kayıt ve ödeme formunda, **zorunlu** |
| 6563 onayı | Ticari iletinin **gönderilmesi** | Ayrı, **isteğe bağlı** kutular |

`marketing_consents` tablosu ikincisini tutar. Kanal başına ayrı satır
(`email` / `sms` / `call`) çünkü İYS onayları kanal bazında kaydediyor.

**Kurallar — bunlara dokunma:**

- Onay kutuları **ASLA önceden işaretli gelmemeli**; işaretli gelen kutu
  geçerli onay sayılmaz ve tüm liste kullanılamaz hâle gelir.
- Onay `consented_at` + `ip_address` + `source` ile saklanır. **İspat yükü
  göndericidedir**; bu üç alanı çıkarma.
- **Kayıtlar SİLİNMEZ.** Çıkış yapan `status='revoked'` olur — çıkış
  talebinin de ispatlanabilmesi gerekiyor.
- Çıkış sayfası **giriş gerektirmez** (`/abonelik/{token}`). Kanun çıkışın
  "kolay ve ücretsiz" olmasını şart koşuyor; e-postadaki bağlantıya tıklayan
  kişi oturum açmış olmayabilir.
- Onay tazelendiğinde/geri çekildiğinde `synced_to_iys_at` **null'a çekilir**:
  değişen onay İYS'ye yeniden yüklenmeli.
- Telefonlar `normalizePhone()` ile tek biçime indirilir (`905321112233`).
  Aynı numaranın iki farklı yazımla kaydedilmesi, "çıktım ama mesaj geliyor"
  şikâyetinin en yaygın sebebi.

**Gönderim yapılmadan önce gerekenler (kod değil, süreç):**

1. Onaylar **İYS'ye yüklenmiş** olmalı — panelden Excel alınıp yüklenir,
   sonra "İYS'ye Yüklendi" ile işaretlenir.
2. Her iletide **çıkış bağlantısı** bulunmalı (`$consent->unsubscribeUrl()`).
3. Sipariş/kargo/stok bildirimleri ticari ileti DEĞİLDİR, bu onaydan
   bağımsızdır — o gönderimleri bu tabloya bağlama.

---

## 10.45. Toplu gönderim: üç ayrı kilit

Kampanya gönderimi (`CampaignSender`) **üç kilidin üçünü birden** geçmeden
çalışmaz. Hiçbirini gevşetme:

1. **Ana şalter** — `settings.marketing_sending_enabled`, varsayılan
   **kapalı**. Kodun yayına alınmasıyla gönderim kendiliğinden açılmamalı.
2. **Kanal yapılandırması** — SMS için `services.netgsm.*` dolu olmalı.
3. **Onay** — alıcı listesi **yalnızca** `marketing_consents` üzerinden
   kurulur. Elle liste verilemez; "yanlış listeye gönderdim" mümkün değil.

Ek güvenceler:

- Her alıcıda **taze onay kontrolü** (`$onay->fresh()`). Uzun gönderim
  sürerken çıkan olursa atlanır — listenin başındaki tek kontrol yetmez.
- **Çıkış bağlantısı gövdeye kodda eklenir** (`CampaignSender::smsMetni()`,
  `emails/marketing/campaign.blade.php`). Panelde yazana bırakılırsa er geç
  unutulur ve o gönderim kanuna aykırı olur.
- `campaign_deliveries` üzerinde `(campaign_id, contact)` **benzersiz**:
  komut yarıda kalıp yeniden çalıştırılırsa kaldığı yerden devam eder,
  kimseye iki kez gitmez.
- Gönderilmiş kampanya **düzenlenemez** — "ne göndermiştik" sorusunun
  cevabı bozulmasın.

### Gönderim neden artisan komutuyla?

Kuyruk `sync` (madde 11). Panelden yüzlerce gönderim denenirse istek zaman
aşımına uğrar ve gönderim yarıda kalır. Bu yüzden panel kampanyayı yalnızca
`queued` yapar, gönderimi komut yürütür:

```bash
php artisan campaigns:send
php artisan netgsm:test 05321112233   # ayar sınama
```

Cron'a bağlanabilir. `QUEUE_CONNECTION=database` + worker'a geçilirse
komut yerine job'a alınabilir.

### SMS metninde Türkçe karakter

`ç ğ ı ö ş ü` kullanılan SMS UCS-2'ye düşer: parça başına 160 yerine
**70 karakter**. Uzun kampanya metinlerinde kontör maliyetini üçe katlar.
Panelde bu uyarı yazılı.

---

## 10.5. Sipariş bildirimi iki kanaldan gider

`OrderFulfiller::notifyAdmin()` hem e-posta hem Telegram gönderir; biri
başarısız olsa da diğeri denenir. Hatalar yalnızca loglanır — **bildirim
gitmedi diye müşterinin siparişi düşmemeli.**

- E-posta adresi: `config('mail.order_notification_address')`.
  Bu, iletişim formunun gittiği `admin_address`'ten **ayrıdır**. Gönderen ve
  alıcı aynı adres olunca (`info@` → `info@`) bazı sağlayıcılar mesajı spam'e
  atıyordu.
- Telegram yapılandırılmamışsa sessizce devre dışıdır; testlerde ve yerelde
  ayrıca bir şey yapmak gerekmez.
- **Yönetici bildirimi sipariş anında gider.** Havale onayında tekrar
  gönderilmez (`sendConfirmationMails($order, notifyAdmin: false)`) — onayı
  yönetici zaten kendisi verdi.
- Yönetici e-postasında **TC Kimlik No yer almaz.** Veritabanında şifreli tutulan
  bir veriyi düz metin e-posta ile göndermek o korumayı anlamsız kılar.

### Yeni müşteri bildirimi (isteğe bağlı)

Sipariş bildiriminden **ayrıdır** ve panelden açılıp kapatılır:
Panel → Site Ayarları → **Bildirimler**. Varsayılan **kapalı**.

- Tetikleyici `UserObserver::created()`. Observer kullanılıyor çünkü kullanıcı
  **dört ayrı yerde** oluşuyor: üyelik formu, Google ile giriş, misafir
  siparişinden üyelik ve `admin:create`. Bildirimi dört yere kopyalasaydık
  beşincisi eklendiğinde unutulurdu.
- `is_admin` olan hesap müşteri sayılmaz, bildirim üretmez.
- Mesajda **yalnızca ad, e-posta ve tarih** var. Telefon/adres bilinçli olarak
  yok: Telegram mesajı sunucu dışına çıkan bir kayıttır, veri minimizasyonu.
- Gönderim hatası üyelik işlemini **düşürmez** (`UserObserver` try/catch).
- `php artisan telegram:test` bu ayarın açık mı kapalı mı olduğunu da yazar.

---

## 10.6. Stok bildirimi model olayına bağlı

"Stok Gelince Haber Ver" kayıtları `stock_notifications` tablosunda tutulur.
Gönderimi tetikleyen tek yer `ProductObserver::updated()`: stok **0'dan yukarı
çıktığı anda** bekleyenlere e-posta gider.

- Bu yalnızca **model üzerinden** yapılan kayıtlarda çalışır. Stoğu bir yerde
  `Product::where(...)->update(...)` ile artırırsan model olayı üretilmez ve
  **kimseye haber gitmez** — orada `StockNotifier`'ı elle çağır.
  (`OrderFulfiller` stoğu yalnızca düşürür, sorun yaratmaz.)
- `notified_at` **yalnızca e-posta gerçekten çıktığında** dolar. SMTP hatasında
  kayıt bekleyen olarak kalır; panelde "bildirildi" görünüp müşteriye hiçbir şey
  gitmemesi en kötü sonuç olurdu.
- Bildirim gönderimindeki hata ürün kaydetmeyi **düşürmez**, yalnızca loglanır.
  Yönetici stoğu girdi, iş bitti.
- `(product_id, email)` **benzersizdir**. Aynı kişi ikinci kez tıklarsa yeni kayıt
  açılmaz; ürün daha önce gelip tekrar tükendiyse `notified_at` sıfırlanır ve
  müşteri ikinci turda da haber alır.
- Panelde **Ürünler → "Stok Bekleyen"** sütunu ve aynı adlı filtre: hangi
  tükenmiş ürünün hazır müşterisi olduğunu bu söyler.
- Uç nokta (`POST /stok-bildirimi`) açıkta e-posta topluyor, `throttle:10,1`
  ile sınırlı. Bu sınırı kaldırma.

---

## 2.5. `landing.blade.php` layout kullanmaz

Portal sayfası (`/`) **bağımsız bir HTML dosyasıdır**; `layouts/app.blade.php`'i
extend etmez ve Alpine yüklemez. Layout'a eklediğin hiçbir şey orada çıkmaz.

> **Geçmiş hata:** Giriş, kayıt ve çıkış üçü de `landing`'e yönlendiriyor ve
> `with('success', ...)` mesajı gönderiyordu. Bildirim kutusu yalnızca
> layout'ta olduğu için mesaj her seferinde üretilip **hiç gösterilmedi.**
> Kutu artık `partials/toast.blade.php` içinde ve iki yere de dâhil ediliyor;
> Alpine'a bağımlı olmaması bu yüzden.

Layout'a bir bileşen eklerken portal sayfasında da gerekip gerekmediğine bak.

---

## 10.7. JS'ten POST: `expectsJson()`, `ajax()` değil

`$request->ajax()` **yalnızca** `X-Requested-With: XMLHttpRequest` başlığına
bakar. `fetch` bu başlığı kendiliğinden göndermez; yalnızca
`Accept: application/json` gönderen bir istek `ajax()` kontrolünden geçemez ve
controller JSON yerine **yönlendirme** döner. Tarayıcıda belirti şudur: AJAX
yaptığını sandığın düğme sessizce sayfayı yeniler.

`expectsJson()` ikisini de kapsar. JS'ten çağrılan uçlarda onu kullan.
(`ProfileController::toggleFavorite()` bu yüzden değiştirildi.)

JS'ten POST atarken CSRF jetonu `<head>`'deki `<meta name="csrf-token">`
etiketinden okunur.

---

## 10.8. Blog: yayın iki koşula bağlı

Bir yazının sitede görünmesi için `is_published` açık **VE** `published_at`
geçmişte olmalı (`Post::published()` scope'u). Tek bir bayrak olsaydı
zamanlanmış yayın yapılamazdı.

- **Yalnızca `Post::published()` üzerinden listele.** Taslak adresin
  paylaşılması ya da site haritasına girmesi Google'da 404 demektir.
- `slug` yayına alındıktan sonra **değiştirilmemeli** — gelen bağlantılar ve
  arama sıralaması ona bağlı. Panelde slug yalnızca yeni kayıtta başlıktan
  otomatik türetilir; düzenlemede dokunulmaz.
- Yazı gövdesi zengin metin editöründen **HTML** olarak gelir ve
  `{!! !!}` ile basılır. Yalnızca yönetici yazabildiği için güvenlidir;
  bu alanı kullanıcıya açarsan önce temizlemen gerekir.
- `channel === 'health'` seçilen yazının altına **tıbbi uyarı otomatik**
  eklenir (`blog/show.blade.php`). Sağlık içeriğinde bu uyarı zorunlu.
- Yazı gövdesinin başlık/liste/bağlantı stilleri `blog/show.blade.php`
  içindeki `.blog-body` bloğundan gelir — Tailwind typography eklentisi yok.

### Kanal sayfasındaki sağ raf

Kategoriler solda, rehber yazıları sağda (`partials/blog_rail.blade.php`).
Aynı parça iki kipte çalışır: `rail` (dikey, sağ kolon) ve `grid` (yatay, sayfa
altı). Raf **yalnızca `xl` ve üstünde** kolon olur; daha küçük ekranda aynı
liste sayfanın altında yatay ızgara olarak basılır.

Ürün ızgarası raf yüzünden `lg:grid-cols-3 2xl:grid-cols-4` oldu: 4. kolon
yalnızca 1536px ve üstünde açılır (bkz. madde 1.5).

Raf, kanalın yazılarının yanında **`general`** kanalındakileri de gösterir —
genel rehberler iki mağazayı da ilgilendirir.

**Sıralama:** `posts.sort_order` (küçük üstte), sonra yayın tarihi.
Panelde satırlar sürüklenerek sıralanır. Hepsi 0 olduğunda davranış eskisi
gibi "yeni üstte" kalır — mevcut yazılar için geriye dönük uyumlu.

**Aynı sıralama `/blog` listesinde de geçerlidir** (`PostController::index`).
İkisi ayrı sıralanırsa "panelde sürükledim ama sayfada değişmedi" sorusu
çıkıyor; `BlogTest` her iki listeyi ayrı ayrı kolluyor.

### Yazı içine görsel

`RichEditor` üzerinde `fileAttachmentsDisk('public')` +
`fileAttachmentsDirectory('posts/icerik')` tanımlı. **Bu üçü olmadan araç
çubuğundaki görsel düğmesi çalışmaz**, yükleme sessizce takılı kalır.

Kapak görseli ayrı alandır (`cover_image`, `posts/` dizini) ve paylaşım
kartlarında (Open Graph) kullanılan görsel odur.

### Menüdeki "Rehberler" bağlantısı

`Post::hasPublished()` yayında yazı yoksa `false` döner ve bağlantı üst menüde,
mobil menüde ve alt bilgide **gizlenir**. Boş sayfaya götüren menü öğesi
"site yarım kalmış" izlenimi veriyor.

Bu sonuç **bilerek önbelleğe alınmadı.** Önbellek denendi; `Post::where(...)
->delete()` gibi sorgu kurucu silmeleri model olayı üretmediği için önbellek
bayat kalıyor ve yazı silinse de menüde bağlantı duruyordu. İndeksli bir
`exists()` sorgusu, layout'un halihazırda yaptığı ayar ve kategori
sorgularının yanında ölçülemeyecek kadar hafif.

---

## 11. Kuyruk `sync`

`QUEUE_CONNECTION=sync`. Filament'in Excel içe/dışa aktarması kuyruk işi olarak
çalışır; `database` yapıp worker çalıştırmazsan **indirmeler sessizce hiç
tamamlanmaz.**

Binlerce satırlık dosyalarla çalışmaya başlarsan `database`'e geç ve
`php artisan queue:work` çalıştır.

---

## 12. Özel komutlar

```bash
php artisan admin:create        # yönetici hesabı oluştur / mevcut hesabı yönetici yap
php artisan locations:import    # il/ilçe/mahalle verisi (--fresh ile sıfırdan)
php artisan telegram:test       # sipariş bildirimi ayarlarını sına
php artisan netgsm:test [tel]   # SMS ayarlarını sına, isteğe bağlı deneme SMS
php artisan campaigns:send      # sıraya alınmış toplu gönderimleri yürüt
```

---

## Değişiklik yapmadan önce

```bash
php artisan test
```

**357 test** var ve hepsi geçmeli. Özellikle ödeme, sepet, kupon ve yetkilendirme
testleri geçmişte gerçek hatalar yakaladı — kırmızı görürsen düzeltmeden push etme.
