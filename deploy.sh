#!/bin/bash
#
# Sunucuda calistirilir:
#   cd ~/domains/wisesolutions.com.tr/public_html && bash deploy.sh
#
# GitHub'daki son surumu ceker, veritabanini gunceller, onbellegi temizler.

set -e  # herhangi bir adim hata verirse dur

cd "$(dirname "$0")"

echo ""
echo "==============================================="
echo "  Canliya Alma"
echo "==============================================="
echo ""

ONCEKI=$(git rev-parse --short HEAD)

echo "--> GitHub'dan cekiliyor..."
git pull origin master

SONRAKI=$(git rev-parse --short HEAD)

if [ "$ONCEKI" = "$SONRAKI" ]; then
    echo ""
    echo "Yeni bir sey yok (surum: $ONCEKI)."
    echo "Bilgisayarindan 'git push' yapmayi unutmus olabilirsin."
    echo ""
    exit 0
fi

echo ""
echo "--> Surum: $ONCEKI -> $SONRAKI"
echo ""

# composer.lock degistiyse bagimliliklari guncelle
if git diff --name-only "$ONCEKI" "$SONRAKI" | grep -q "composer.lock"; then
    echo "--> Yeni paketler var, composer calisiyor..."
    composer install --no-dev --optimize-autoloader --no-interaction
    echo ""
fi

echo "--> Veritabani guncelleniyor..."
php artisan migrate --force
echo ""

echo "--> Onbellek temizleniyor..."
php artisan config:clear
php artisan view:clear
php artisan route:clear
echo ""

# Gorsel klasoru baglantisi yoksa olustur
if [ ! -e "public/storage" ]; then
    echo "--> Gorsel klasor baglantisi olusturuluyor..."
    ln -s "$(pwd)/storage/app/public" "$(pwd)/public/storage"
    echo ""
fi

echo "==============================================="
echo "  TAMAM - canliya alindi."
echo "==============================================="
echo ""
