# Buy Wisely — Çalışma Notları

Bu dosya, projeyi güncellerken ihtiyacın olan her şeyi içerir.
Bir şeyi unutursan buraya bak.

> **Kod yazacaksan** önce [`GELISTIRICI-NOTLARI.md`](GELISTIRICI-NOTLARI.md) dosyasına
> bak — bu projeye özgü teknik tuzaklar orada.

---

## 1. Kod nasıl akıyor

```
SENİN BİLGİSAYARIN  ──push──►  GitHub  ──pull──►  SUNUCU
   (kod burada yazılır)                        (sadece alır)
```

**Sunucuda asla `git add`, `git commit`, `git push` yapma.** Sunucu sadece alır.

- Proje klasörü (bilgisayarında): `C:\xampp\htdocs\eticaret`
- GitHub: https://github.com/mustafabmarasli/wiseWebSitesi
- Canlı site: https://wisesolutions.com.tr

---

## 2. Değişiklik yaptım, nasıl yayınlarım?

### Kolay yol (önerilen)

Proje klasöründe çift tıkla: **`gonder.bat`**

Bu betik sırayla: testleri çalıştırır → hata varsa durur → commit mesajını sorar → GitHub'a gönderir.

### Elle yapmak istersen

```bash
php artisan test
git add .
git commit -m "ne yaptigini anlatan kisa mesaj"
git push
```

> İlk kez push ediyorsan bir kez: `git push --set-upstream origin master`

---

## 3. Sunucuya bağlanma (SSH)

Terminal aç (Windows Terminal / PowerShell / Git Bash):

```bash
ssh -p 65002 u894234722@82.198.229.84
```

| Bilgi | Değer |
|---|---|
| IP | `82.198.229.84` |
| Port | `65002` |
| Kullanıcı | `u894234722` |
| Şifre | Hostinger hPanel → Advanced → SSH Access |

Şifre yazarken ekranda **hiçbir şey görünmez**, bu normaldir. Yaz ve Enter'a bas.

Proje klasörü sunucuda:
```
~/domains/wisesolutions.com.tr/public_html
```

Çıkmak için: `exit`

---

## 4. Sunucuya yayınlama (SSH'a girdikten sonra)

### Kolay yol

```bash
cd ~/domains/wisesolutions.com.tr/public_html && bash deploy.sh
```

### Elle yapmak istersen

```bash
cd ~/domains/wisesolutions.com.tr/public_html
git pull origin master
php artisan migrate --force
php artisan config:clear && php artisan view:clear && php artisan route:clear
```

`git pull` **"Already up to date"** diyorsa: bilgisayarından push etmeyi unutmuşsundur.

---

## 4.5. Sunucuda `.env` düzenleme

`.env` git'e girmez — bilgisayarındaki değişiklik sunucuya **gitmez**, elle yapman gerekir.

Önce klasöre gir:

```bash
cd ~/domains/wisesolutions.com.tr/public_html
```

### Yeni bir satır ekleyeceksen (en kolayı)

Önce gerçekten yok mu bak:

```bash
grep MAIL_ORDER_NOTIFY_ADDRESS .env
```

Hiçbir şey yazmadıysa yok demektir, sona ekle:

```bash
echo 'MAIL_ORDER_NOTIFY_ADDRESS="mustafabmarasli@gmail.com"' >> .env
```

> `>>` ekler, `>` **dosyanın tamamını siler**. Tek `>` yazma.

### Var olan bir satırı değiştireceksen (nano)

```bash
nano .env
```

- Ok tuşlarıyla gez, normal yazı yazar gibi düzenle
- Kaydet: **Ctrl+O** → **Enter**
- Çık: **Ctrl+X**
- Vazgeç: **Ctrl+X** → **N**

Fare çalışmaz, kopyala-yapıştır için sağ tık kullan.

### Her değişiklikten sonra

```bash
php artisan config:clear
```

Bunu yapmazsan Laravel eski değeri kullanmaya devam eder.

Kontrol:

```bash
grep -E "^MAIL_|^TELEGRAM_" .env
```

---

## 5. Sık kullanılan komutlar (sunucuda)

| Ne yapar | Komut |
|---|---|
| Yönetici hesabı oluştur | `php artisan admin:create` |
| İl/ilçe/mahalle yükle | `php artisan locations:import` |
| Görsel klasör bağlantısı | `ln -s ~/domains/wisesolutions.com.tr/public_html/storage/app/public ~/domains/wisesolutions.com.tr/public_html/public/storage` |
| Ayarları kontrol et | `grep -E "^(APP_ENV\|APP_DEBUG\|IYZICO_BASE_URL)=" .env` |
| Hata kayıtlarına bak | `tail -50 storage/logs/laravel.log` |
| Migration durumu | `php artisan migrate:status` |

---

## 6. Görseller

**Görseller git'e girmez.** Bilgisayarında yüklediğin görsel sunucuya gitmez, tersi de geçerli.

- Ürün görsellerini **canlı panelden** yükle: https://wisesolutions.com.tr/admin
- Toplu yükleme: Admin → **Toplu Görsel Yükle**
- Görseller otomatik **WebP**'ye çevrilir ve küçültülür (tarayıcıda, yüklemeden önce)

---

## 6.5. Sipariş bildirimleri

Her yeni siparişte **iki kanaldan** haber verilir. Biri çalışmazsa diğeri devrede kalsın diye ikisi de var.

**E-posta** — sunucudaki `.env`'de:

```
MAIL_ADMIN_ADDRESS="info@wisesolutions.com.tr"      ← iletişim formu buraya gider
MAIL_ORDER_NOTIFY_ADDRESS="mustafabmarasli@gmail.com" ← sipariş uyarısı buraya gider
```

İkisi ayrı olmalı: gönderen ve alıcı aynı adres olduğunda (`info@` → `info@`) bazı sağlayıcılar mesajı spam'e atıyor.

**Telegram** — anlık, telefona bildirim düşer:

1. Telegram'da **@BotFather**'a yaz, `/newbot` gönder, bir isim ver. Sana bir **token** verir.
2. Oluşturduğun bota bir mesaj at (örn. "merhaba").
3. Tarayıcıda aç: `https://api.telegram.org/bot<TOKEN>/getUpdates`
4. Dönen metinde `"chat":{"id":123456789` — o sayı senin **chat id**'in.
5. Sunucudaki `.env`'e ekle:

```
TELEGRAM_BOT_TOKEN=1234567890:AAH...
TELEGRAM_CHAT_ID=123456789
```

6. Sunucuda dene:

```bash
php artisan telegram:test
```

Boş bırakırsan Telegram bildirimi gönderilmez, e-posta çalışmaya devam eder.

---

## 6.6. Havale / EFT ile satış

Panel → **Site Ayarları → Ödeme Yöntemleri**:

- **Hesap Adı / Şirket Tam Ünvanı** ve **IBAN** doldurulmadan havale seçeneği müşteriye gösterilmez ve ödeme sayfası kapalı kalır.
- **Havale / EFT İndirimi**: yüzde. İstemiyorsan 0 yaz.
- **Kredi / Banka Kartı**: iyzico onaylanınca burayı aç.

**Para geldiğinde:** Panel → Siparişler → siparişi aç → **"Ödeme Geldi, Onayla"**.
Stok ancak o düğmeye basınca düşer. **Onaylamadan kargoya verme.**

Sipariş numaraları `260729-WISE-K4M2` biçiminde. Müşteri havale açıklamasına bunu yazar; banka ekstresinde bu numarayla eşleştirirsin.

---

## 7. Canlıya çıkmadan kontrol listesi

Sunucudaki `.env` dosyasında:

```
APP_ENV=production
APP_DEBUG=false                              ← true ise tüm şifreler hata sayfasında görünür
APP_URL=https://wisesolutions.com.tr
IYZICO_BASE_URL=https://api.iyzipay.com      ← sandbox ise para tahsil EDİLMEZ
IYZICO_API_KEY=...                           ← canlı anahtar
IYZICO_SECRET_KEY=...                        ← canlı anahtar
MAIL_MAILER=smtp
```

> **`APP_KEY`'i asla değiştirme ve yedekle.** TC kimlik numaraları onunla şifreleniyor; değişirse okunamaz hâle gelirler.

---

## 8. Bir şey ters giderse

**Site açılmıyor / 500 hatası**
```bash
tail -50 storage/logs/laravel.log
```

**Değişiklik canlıda görünmüyor**
```bash
php artisan config:clear && php artisan view:clear && php artisan route:clear
```

**Yeni paket eklediysen**
```bash
composer install --no-dev --optimize-autoloader
```

**Son yayına geri dön** (acil durum)
```bash
git log --oneline -5          # commit listesini gör
git reset --hard <commit-id>  # o hale dön
```
