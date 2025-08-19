#!/bin/bash

# 📦 ESP32 Flood Warning System - Create Download Package
# Author: Emergent AI
# Creates downloadable package of all project files

echo "📦 ESP32 FLOOD WARNING SYSTEM - CREATE DOWNLOAD PACKAGE"
echo "======================================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Check current directory
current_dir=$(basename "$PWD")
print_info "Current directory: $PWD"

# Create package name with timestamp
timestamp=$(date +"%Y%m%d_%H%M%S")
package_name="ESP32-Flood-Warning-System-${timestamp}"

print_info "Creating package: ${package_name}"
echo ""

# Create temporary directory structure
print_info "Step 1: Creating package structure..."
mkdir -p "/tmp/${package_name}"

# Copy files with proper structure
cp -r esp32_flood_warning "/tmp/${package_name}/" 2>/dev/null || true
cp -r esp32_flood_warning_web "/tmp/${package_name}/" 2>/dev/null || true
cp -r web_dashboard "/tmp/${package_name}/" 2>/dev/null || true
cp -r docs "/tmp/${package_name}/" 2>/dev/null || true

# Copy root files
cp README.md "/tmp/${package_name}/" 2>/dev/null || true
cp GITHUB_PUSH_GUIDE.md "/tmp/${package_name}/" 2>/dev/null || true  
cp libraries.txt "/tmp/${package_name}/" 2>/dev/null || true
cp quick_push.sh "/tmp/${package_name}/" 2>/dev/null || true
cp quick_push.bat "/tmp/${package_name}/" 2>/dev/null || true

print_status "Files copied to temporary structure"

# Create different archive formats
print_info "Step 2: Creating archives..."

# Create TAR.GZ (best compression, cross-platform)
cd /tmp
if tar -czf "${package_name}.tar.gz" "${package_name}"; then
    print_status "TAR.GZ created: ${package_name}.tar.gz"
else
    print_warning "Failed to create TAR.GZ"
fi

# Create ZIP if zip command available
if command -v zip &> /dev/null; then
    if zip -r "${package_name}.zip" "${package_name}" > /dev/null 2>&1; then
        print_status "ZIP created: ${package_name}.zip"
    else
        print_warning "Failed to create ZIP"
    fi
else
    print_warning "ZIP command not available, only TAR.GZ created"
fi

# Move archives to original directory
mv "${package_name}.tar.gz" "$OLDPWD/" 2>/dev/null || true
mv "${package_name}.zip" "$OLDPWD/" 2>/dev/null || true

# Cleanup
rm -rf "/tmp/${package_name}"

cd "$OLDPWD"

echo ""
print_info "Step 3: Package information..."

# Show created files
if [ -f "${package_name}.tar.gz" ]; then
    size=$(du -h "${package_name}.tar.gz" | cut -f1)
    print_status "TAR.GZ Package: ${package_name}.tar.gz (${size})"
fi

if [ -f "${package_name}.zip" ]; then
    size=$(du -h "${package_name}.zip" | cut -f1)
    print_status "ZIP Package: ${package_name}.zip (${size})"
fi

echo ""
print_info "Package contents:"
if [ -f "${package_name}.tar.gz" ]; then
    echo "📄 Files included in package:"
    tar -tzf "${package_name}.tar.gz" | head -20
    echo "... (and more files)"
fi

echo ""
print_status "🎉 Download packages created successfully!"
echo ""
print_info "Next steps:"
echo "1. Download the .tar.gz or .zip file to your computer"
echo "2. Extract the archive"
echo "3. Follow the installation guides in docs/ folder"
echo "4. Use quick_push scripts to upload to GitHub"
echo ""

# Create extraction instructions
cat > "EXTRACTION_INSTRUCTIONS.txt" << 'EOF'
📦 ESP32 Flood Warning System - Extraction Instructions

For TAR.GZ file:
================
Windows (with 7-Zip):
- Right-click → 7-Zip → Extract Here

Windows (with WinRAR):
- Right-click → Extract Here

Mac:
- Double-click the .tar.gz file
- Or: tar -xzf ESP32-Flood-Warning-System-*.tar.gz

Linux:
- tar -xzf ESP32-Flood-Warning-System-*.tar.gz

For ZIP file:
=============
Windows:
- Right-click → Extract All

Mac:
- Double-click the .zip file

Linux:
- unzip ESP32-Flood-Warning-System-*.zip

After extraction:
================
1. Open the extracted folder
2. Read README.md for project overview
3. Follow guides in docs/ folder
4. Use Arduino IDE to upload .ino files to ESP32
5. Setup web dashboard using web_dashboard/ files

Happy coding! 🚀
EOF

print_status "Extraction instructions created: EXTRACTION_INSTRUCTIONS.txt"