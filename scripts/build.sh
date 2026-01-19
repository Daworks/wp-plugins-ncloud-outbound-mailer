#!/bin/bash
#
# Build script for Ncloud Outbound Mailer WordPress Plugin
# Creates a distribution-ready zip file
#
# Usage: ./scripts/build.sh
#

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Plugin info
PLUGIN_SLUG="ncloud-outbound-mailer"
VERSION=$(grep "Stable tag:" readme.txt | awk '{print $3}')

if [ -z "$VERSION" ]; then
    VERSION=$(grep "Version:" ncloud-outbound-mailer.php | head -1 | awk '{print $3}')
fi

echo -e "${GREEN}Building ${PLUGIN_SLUG} v${VERSION}${NC}"
echo "================================================"

# Create build directory
BUILD_DIR="build"
DIST_DIR="dist"
PLUGIN_DIR="${BUILD_DIR}/${PLUGIN_SLUG}"

# Clean previous builds
echo -e "${YELLOW}Cleaning previous builds...${NC}"
rm -rf "${BUILD_DIR}"
rm -rf "${DIST_DIR}"
mkdir -p "${PLUGIN_DIR}"
mkdir -p "${DIST_DIR}"

# Copy plugin files
echo -e "${YELLOW}Copying plugin files...${NC}"

# Main plugin file
cp ncloud-outbound-mailer.php "${PLUGIN_DIR}/"

# Include directories
cp -r includes "${PLUGIN_DIR}/"
cp -r admin "${PLUGIN_DIR}/"
cp -r languages "${PLUGIN_DIR}/"

# Documentation
cp readme.txt "${PLUGIN_DIR}/"
cp README.md "${PLUGIN_DIR}/"
cp uninstall.php "${PLUGIN_DIR}/"

# Composer files for autoload (production only)
cp composer.json "${PLUGIN_DIR}/"

# Install production dependencies only
echo -e "${YELLOW}Installing production dependencies...${NC}"
cd "${PLUGIN_DIR}"
composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || {
    echo -e "${YELLOW}No production dependencies to install${NC}"
    rm -f composer.json composer.lock
}
cd - > /dev/null

# Remove unnecessary files from vendor if exists
if [ -d "${PLUGIN_DIR}/vendor" ]; then
    echo -e "${YELLOW}Cleaning vendor directory...${NC}"
    find "${PLUGIN_DIR}/vendor" -type f -name "*.md" -delete 2>/dev/null || true
    find "${PLUGIN_DIR}/vendor" -type f -name "*.txt" -delete 2>/dev/null || true
    find "${PLUGIN_DIR}/vendor" -type f -name "phpunit.xml*" -delete 2>/dev/null || true
    find "${PLUGIN_DIR}/vendor" -type d -name "tests" -exec rm -rf {} + 2>/dev/null || true
    find "${PLUGIN_DIR}/vendor" -type d -name "test" -exec rm -rf {} + 2>/dev/null || true
    find "${PLUGIN_DIR}/vendor" -type d -name ".git" -exec rm -rf {} + 2>/dev/null || true
fi

# Remove composer files if no vendor
if [ ! -d "${PLUGIN_DIR}/vendor" ]; then
    rm -f "${PLUGIN_DIR}/composer.json"
    rm -f "${PLUGIN_DIR}/composer.lock"
fi

# Create zip file
echo -e "${YELLOW}Creating zip archive...${NC}"
cd "${BUILD_DIR}"
zip -r "../${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" "${PLUGIN_SLUG}" -x "*.DS_Store" -x "*__MACOSX*"
cd - > /dev/null

# Calculate file size
ZIP_SIZE=$(ls -lh "${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip" | awk '{print $5}')

# Cleanup build directory
rm -rf "${BUILD_DIR}"

echo ""
echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}Build complete!${NC}"
echo -e "${GREEN}================================================${NC}"
echo ""
echo -e "Output: ${YELLOW}${DIST_DIR}/${PLUGIN_SLUG}-${VERSION}.zip${NC}"
echo -e "Size:   ${YELLOW}${ZIP_SIZE}${NC}"
echo ""
echo -e "To install:"
echo -e "  1. Go to WordPress Admin > Plugins > Add New > Upload Plugin"
echo -e "  2. Choose the zip file and click 'Install Now'"
echo ""
