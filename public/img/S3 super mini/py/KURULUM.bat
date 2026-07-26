@echo off
chcp 65001 >nul
echo ============================================
echo   Trendyol Gorsel Araci - KURULUM
echo   (Bunu sadece BIR KERE calistirman yeterli)
echo ============================================
echo.

python --version >nul 2>&1
if errorlevel 1 (
    echo [HATA] Python bulunamadi.
    echo.
    echo Once Python kur: https://www.python.org/downloads/
    echo Kurulum ekraninda "Add Python to PATH" kutusunu MUTLAKA isaretle.
    echo Kurduktan sonra bu dosyayi tekrar calistir.
    echo.
    pause
    exit /b
)

echo Python bulundu. Gerekli paketler kuruluyor...
echo.
python -m pip install --upgrade pip
python -m pip install numpy opencv-python pillow pillow-heif

echo.
echo ============================================
echo   Kurulum bitti!
echo   Artik CALISTIR.bat dosyasini kullanabilirsin.
echo ============================================
pause
