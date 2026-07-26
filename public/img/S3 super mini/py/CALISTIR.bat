@echo off
chcp 65001 >nul
cd /d "%~dp0"
echo ============================================
echo   Trendyol Gorsel Araci
echo   Bu klasordeki tum gorseller isleniyor...
echo ============================================
echo.
python "%~dp0trendyol_gorsel.py"
