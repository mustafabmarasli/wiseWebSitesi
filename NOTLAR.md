# Buy Wisely — Çalışma Notları

Bu dosya, projeyi güncellerken ihtiyacın olan her şeyi içerir.
Bir şeyi unutursan buraya bak.

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
