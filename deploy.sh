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

echo ""

if [ "$ONCEKI" = "$SONRAKI" ]; then
    # ONEMLI: Burada CIKMIYORUZ. Betikten once elle "git pull" calistirilmis
    # olabilir; o durumda kod guncel ama migration hic calismamis olur.
    # Migration ve onbellek temizligi zaten tekrar calistirilabilir islemler.
    echo "Pull yeni bir sey getirmedi (surum: $ONCEKI)."
    echo "Kod zaten guncel olabilir; yine de migration ve onbellek kontrol edilecek."
else
    echo "--> Surum: $ONCEKI -> $SONRAKI"

    # composer.lock degistiyse bagimliliklari guncelle
    if git diff --name-only "$ONCEKI" "$SONRAKI" | grep -q "composer.lock"; then
        echo ""
        echo "--> Yeni paketler var, composer calisiyor..."
        composer install --no-dev --optimize-autoloader --no-interaction
    fi
fi

echo ""

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
