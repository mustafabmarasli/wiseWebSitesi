# Geliştirici Notları

Bu projede kod yazarken **kaybedilmiş saatler** pahasına öğrenilmiş şeyler.
Yeni bir özelliğe başlamadan önce ilgili bölüme göz at.

Günlük işletme komutları (SSH, deploy, yayınlama) için → [`NOTLAR.md`](NOTLAR.md)

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

`PaymentController::callback()` içinde bilinçli olarak duran korumalar:

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

## 9. Ayarlar veritabanında, kodda değil

Kargo ücreti, ücretsiz kargo limiti ve **site duyurusu** `settings` tablosunda tek
satırda tutulur → Panel → **Site Ayarları**.

Yani bunları değiştirmek için deploy gerekmez. Ama tersi de doğru:
**kodu canlıya atmak ayarı açmaz** — canlı panelden ayrıca açman gerekir.

`Setting::current()` her zaman bir satır döndürür (yoksa oluşturur).

---

## 10. KVKK / gizlilik

- **TC Kimlik No şifreli saklanır** (`'identity_number' => 'encrypted'` cast).
  **`APP_KEY` değişirse tüm kayıtlı kimlik numaraları okunamaz hâle gelir.**
  Yedekle, asla değiştirme.
- Görüntülenme kaydında IP ham saklanmaz, `sha256` ile hash'lenir.
- Sipariş dışa aktarımı (CSV) kişisel veri içerir — dosyayı paylaşırken dikkat.
- `UserExporter` parola hash'i ve `remember_token` içermez; **eklemeyin.**

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
```

---

## Değişiklik yapmadan önce

```bash
php artisan test
```

**116 test** var ve hepsi geçmeli. Özellikle ödeme, sepet, kupon ve yetkilendirme
testleri geçmişte gerçek hatalar yakaladı — kırmızı görürsen düzeltmeden push etme.
