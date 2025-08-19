#!/bin/bash

# 🚀 ESP32 Flood Warning System - Quick GitHub Push Script
# Author: Emergent AI
# Usage: ./quick_push.sh

echo "🌊 ESP32 FLOOD WARNING SYSTEM - GITHUB PUSH"
echo "============================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if git is installed
if ! command -v git &> /dev/null; then
    print_error "Git tidak ditemukan! Please install Git first."
    exit 1
fi

# Prompt untuk GitHub repository URL
echo -e "${BLUE}📝 Setup GitHub Repository${NC}"
echo "Contoh: https://github.com/username/ESP32-Flood-Warning-System.git"
echo ""
read -p "Masukkan GitHub Repository URL: " REPO_URL

if [ -z "$REPO_URL" ]; then
    print_error "Repository URL tidak boleh kosong!"
    exit 1
fi

echo ""
print_info "Repository URL: $REPO_URL"
echo ""

# Ask for confirmation
read -p "Lanjutkan push ke repository ini? (y/N): " confirm
if [[ $confirm != [yY] ]]; then
    echo "Push dibatalkan."
    exit 0
fi

echo ""
print_info "Memulai proses push ke GitHub..."
echo ""

# Step 1: Initialize Git (if not already initialized)
print_info "Step 1: Initializing Git repository..."
if [ ! -d ".git" ]; then
    git init
    print_status "Git repository initialized"
else
    print_status "Git repository already exists"
fi

# Step 2: Configure Git (if not configured)
GIT_USER=$(git config --global user.name)
GIT_EMAIL=$(git config --global user.email)

if [ -z "$GIT_USER" ] || [ -z "$GIT_EMAIL" ]; then
    echo ""
    print_warning "Git belum dikonfigurasi. Mari setup dulu:"
    read -p "Masukkan nama Anda: " user_name
    read -p "Masukkan email Anda: " user_email
    
    git config --global user.name "$user_name"
    git config --global user.email "$user_email"
    print_status "Git configuration completed"
fi

# Step 3: Add all files
print_info "Step 2: Adding all files to git..."
git add .
print_status "All files added"

# Step 4: Commit files
print_info "Step 3: Committing files..."
commit_message="ESP32 Flood Warning System v1.0 - Complete Implementation

Features:
- ESP32 Arduino code with dual connectivity (Blynk + Web Dashboard)
- Custom web dashboard with PHP backend and HTML/CSS/JS frontend
- Real-time monitoring and control system
- Solar power management
- 3-level warning system (1m, 2m, 3m thresholds)
- Complete documentation and installation guides

Components:
- Hardware setup and wiring guides
- Blynk IoT integration
- Custom web dashboard with database
- Power management system
- Installation and troubleshooting guides"

git commit -m "$commit_message"
print_status "Files committed"

# Step 5: Add remote origin (if not exists)
print_info "Step 4: Setting up remote repository..."
if git remote get-url origin &> /dev/null; then
    print_warning "Remote origin sudah ada, updating URL..."
    git remote set-url origin "$REPO_URL"
else
    git remote add origin "$REPO_URL"
fi
print_status "Remote repository configured"

# Step 6: Push to GitHub
print_info "Step 5: Pushing to GitHub..."
git branch -M main

# Try to push
if git push -u origin main; then
    echo ""
    print_status "🎉 BERHASIL! Files sudah di-push ke GitHub!"
    echo ""
    print_info "Repository Anda bisa diakses di:"
    echo -e "${GREEN}🌐 $REPO_URL${NC}"
    echo ""
    print_info "Next Steps:"
    echo "1. Buka repository di browser untuk verifikasi"
    echo "2. Add description dan topics di GitHub"
    echo "3. Setup README.md jika diperlukan"
    echo "4. Invite collaborators jika ada"
    echo ""
else
    echo ""
    print_error "Push gagal! Kemungkinan penyebab:"
    echo "1. Repository URL salah"
    echo "2. Belum login ke GitHub"
    echo "3. Tidak ada permission ke repository"
    echo ""
    print_info "Solusi:"
    echo "1. Pastikan repository URL benar"
    echo "2. Setup Personal Access Token:"
    echo "   - GitHub → Settings → Developer settings → Personal access tokens"
    echo "   - Generate new token dengan scope 'repo'"
    echo "   - Gunakan token sebagai password saat login"
    echo ""
    print_info "Coba push manual dengan:"
    echo "git push -u origin main"
    exit 1
fi