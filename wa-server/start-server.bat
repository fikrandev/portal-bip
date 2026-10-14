@echo off
title WA Gateway eportal - Server WhatsApp Mandiri
cd /d %~dp0
echo ========================================================
echo   WA Gateway eportal - Server WhatsApp Mandiri
echo   Portal BIP - Yayasan Bina Insan Palu
echo ========================================================
echo Memeriksa Node.js...
node -v >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Node.js tidak ditemukan di sistem! Silakan install Node.js terlebih dahulu.
    pause
    exit /b 1
)

echo Menjalankan Server WA Gateway eportal pada port 8001...
node server.js
if %errorlevel% neq 0 (
    echo [ERROR] Server berhenti secara tidak normal.
    pause
)
