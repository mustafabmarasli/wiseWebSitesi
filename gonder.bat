@echo off
chcp 65001 >nul
setlocal

cd /d "%~dp0"

echo.
echo ===============================================
echo   GitHub'a Gonder
echo ===============================================
echo.

REM --- 1. Degisiklik var mi? ---
git diff --quiet && git diff --cached --quiet
if %errorlevel% equ 0 (
    echo Degisiklik yok - gonderilecek bir sey bulunamadi.
    echo.
    pause
    exit /b 0
)

echo Degisen dosyalar:
echo.
git status --short
echo.

REM --- 2. Testler ---
echo -----------------------------------------------
echo   Testler calisiyor...
echo -----------------------------------------------
echo.
call php artisan test
if %errorlevel% neq 0 (
    echo.
    echo ===============================================
    echo   TESTLER BASARISIZ - gonderim iptal edildi.
    echo   Hatalari duzeltip tekrar deneyin.
    echo ===============================================
    echo.
    pause
    exit /b 1
)

REM --- 3. Commit mesaji ---
echo.
set "MESAJ="
set /p MESAJ="Ne yaptin? (kisa aciklama): "

if "%MESAJ%"=="" (
    echo.
    echo Mesaj bos birakilamaz - iptal edildi.
    echo.
    pause
    exit /b 1
)

REM --- 4. Kaydet ve gonder ---
echo.
git add .
git commit -m "%MESAJ%"
if %errorlevel% neq 0 (
    echo.
    echo Commit basarisiz.
    pause
    exit /b 1
)

echo.
echo Gonderiliyor...
git push
if %errorlevel% neq 0 (
    echo.
    echo Push basarisiz. Ilk kez gonderiyorsan sunu deneyin:
    echo    git push --set-upstream origin master
    echo.
    pause
    exit /b 1
)

echo.
echo ===============================================
echo   BASARILI - GitHub'a gonderildi.
echo.
echo   Simdi canliya almak icin sunucuya baglan:
echo     ssh -p 65002 u894234722@82.198.229.84
echo.
echo   Sonra:
echo     cd ~/domains/wisesolutions.com.tr/public_html
echo     bash deploy.sh
echo ===============================================
echo.
pause
