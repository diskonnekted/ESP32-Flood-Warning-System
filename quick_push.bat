@echo off
chcp 65001 >nul
title ESP32 Flood Warning System - GitHub Push

echo 🌊 ESP32 FLOOD WARNING SYSTEM - GITHUB PUSH
echo =============================================
echo.

REM Check if git is installed
git --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Git tidak ditemukan! Please install Git first.
    echo Download dari: https://git-scm.com/
    pause
    exit /b 1
)

echo 📝 Setup GitHub Repository
echo Contoh: https://github.com/username/ESP32-Flood-Warning-System.git
echo.
set /p REPO_URL="Masukkan GitHub Repository URL: "

if "%REPO_URL%"=="" (
    echo ❌ Repository URL tidak boleh kosong!
    pause
    exit /b 1
)

echo.
echo ℹ️  Repository URL: %REPO_URL%
echo.

set /p confirm="Lanjutkan push ke repository ini? (y/N): "
if /i not "%confirm%"=="y" (
    echo Push dibatalkan.
    pause
    exit /b 0
)

echo.
echo ℹ️  Memulai proses push ke GitHub...
echo.

REM Step 1: Initialize Git (if not already initialized)
echo ℹ️  Step 1: Initializing Git repository...
if not exist ".git" (
    git init
    echo ✅ Git repository initialized
) else (
    echo ✅ Git repository already exists
)

REM Step 2: Configure Git (if not configured)
for /f "delims=" %%i in ('git config --global user.name 2^>nul') do set GIT_USER=%%i
for /f "delims=" %%i in ('git config --global user.email 2^>nul') do set GIT_EMAIL=%%i

if "%GIT_USER%"=="" (
    echo.
    echo ⚠️  Git belum dikonfigurasi. Mari setup dulu:
    set /p user_name="Masukkan nama Anda: "
    set /p user_email="Masukkan email Anda: "
    
    git config --global user.name "!user_name!"
    git config --global user.email "!user_email!"
    echo ✅ Git configuration completed
)

REM Step 3: Add all files
echo ℹ️  Step 2: Adding all files to git...
git add .
echo ✅ All files added

REM Step 4: Commit files
echo ℹ️  Step 3: Committing files...
git commit -m "ESP32 Flood Warning System v1.0 - Complete Implementation"
echo ✅ Files committed

REM Step 5: Add remote origin
echo ℹ️  Step 4: Setting up remote repository...
git remote get-url origin >nul 2>&1
if errorlevel 1 (
    git remote add origin "%REPO_URL%"
) else (
    echo ⚠️  Remote origin sudah ada, updating URL...
    git remote set-url origin "%REPO_URL%"
)
echo ✅ Remote repository configured

REM Step 6: Push to GitHub
echo ℹ️  Step 5: Pushing to GitHub...
git branch -M main
git push -u origin main

if errorlevel 1 (
    echo.
    echo ❌ Push gagal! Kemungkinan penyebab:
    echo 1. Repository URL salah
    echo 2. Belum login ke GitHub
    echo 3. Tidak ada permission ke repository
    echo.
    echo ℹ️  Solusi:
    echo 1. Pastikan repository URL benar
    echo 2. Setup Personal Access Token:
    echo    - GitHub → Settings → Developer settings → Personal access tokens
    echo    - Generate new token dengan scope 'repo'
    echo    - Gunakan token sebagai password saat login
    echo.
    echo ℹ️  Coba push manual dengan: git push -u origin main
    pause
    exit /b 1
) else (
    echo.
    echo ✅ 🎉 BERHASIL! Files sudah di-push ke GitHub!
    echo.
    echo ℹ️  Repository Anda bisa diakses di:
    echo 🌐 %REPO_URL%
    echo.
    echo ℹ️  Next Steps:
    echo 1. Buka repository di browser untuk verifikasi
    echo 2. Add description dan topics di GitHub
    echo 3. Setup README.md jika diperlukan
    echo 4. Invite collaborators jika ada
    echo.
)

echo.
echo Tekan any key untuk close...
pause >nul