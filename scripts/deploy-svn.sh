#!/bin/bash
#
# Deploy script for WordPress.org SVN repository
# Deploys the plugin to WordPress.org plugin directory
#
# Usage: ./scripts/deploy-svn.sh
#

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Configuration
PLUGIN_SLUG="ncloud-outbound-mailer"
SVN_URL="https://plugins.svn.wordpress.org/${PLUGIN_SLUG}"
SVN_USERNAME="dhlee7"
SVN_PASSWORD="svn_rsZPTbwgK61ejwT6HXdKLXDDZjONncq75a3c78d7"

# Get version from plugin file
VERSION=$(grep "Version:" ncloud-outbound-mailer.php | head -1 | awk '{print $3}')

if [ -z "$VERSION" ]; then
    echo -e "${RED}Error: Could not determine version${NC}"
    exit 1
fi

echo -e "${GREEN}Deploying ${PLUGIN_SLUG} v${VERSION} to WordPress.org${NC}"
echo "========================================================"

# Confirm deployment
echo ""
echo -e "${YELLOW}This will deploy version ${VERSION} to WordPress.org SVN.${NC}"
read -p "Continue? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}Deployment cancelled.${NC}"
    exit 1
fi

# Create temp directory
SVN_DIR=$(mktemp -d)
echo -e "${YELLOW}Working directory: ${SVN_DIR}${NC}"

# Cleanup function
cleanup() {
    echo -e "${YELLOW}Cleaning up...${NC}"
    rm -rf "${SVN_DIR}"
}
trap cleanup EXIT

# Checkout SVN repository
echo -e "${YELLOW}Checking out SVN repository...${NC}"
svn checkout "${SVN_URL}" "${SVN_DIR}" --username "${SVN_USERNAME}" --password "${SVN_PASSWORD}" --non-interactive --trust-server-cert-failures=unknown-ca 2>/dev/null || {
    echo -e "${YELLOW}Checkout with depth empty for faster operation...${NC}"
    svn checkout "${SVN_URL}" "${SVN_DIR}" --depth=immediates --username "${SVN_USERNAME}" --password "${SVN_PASSWORD}" --non-interactive --trust-server-cert-failures=unknown-ca
    svn update "${SVN_DIR}/trunk" --set-depth=infinity --username "${SVN_USERNAME}" --password "${SVN_PASSWORD}" --non-interactive
    svn update "${SVN_DIR}/tags" --set-depth=immediates --username "${SVN_USERNAME}" --password "${SVN_PASSWORD}" --non-interactive
}

# Check if tag already exists
if [ -d "${SVN_DIR}/tags/${VERSION}" ]; then
    echo -e "${RED}Error: Tag ${VERSION} already exists!${NC}"
    echo "Please update the version number before deploying."
    exit 1
fi

# Clear trunk
echo -e "${YELLOW}Clearing trunk...${NC}"
rm -rf "${SVN_DIR}/trunk/"*

# Copy files to trunk
echo -e "${YELLOW}Copying files to trunk...${NC}"

# Main plugin file
cp ncloud-outbound-mailer.php "${SVN_DIR}/trunk/"

# Directories
cp -r includes "${SVN_DIR}/trunk/"
cp -r admin "${SVN_DIR}/trunk/"
cp -r languages "${SVN_DIR}/trunk/"

# Documentation
cp readme.txt "${SVN_DIR}/trunk/"
cp README.md "${SVN_DIR}/trunk/"
cp uninstall.php "${SVN_DIR}/trunk/"

# Composer for autoload
cp composer.json "${SVN_DIR}/trunk/"

# Install production dependencies
echo -e "${YELLOW}Installing production dependencies...${NC}"
cd "${SVN_DIR}/trunk"
composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null || {
    echo -e "${YELLOW}No production dependencies${NC}"
    rm -f composer.json composer.lock
}
cd - > /dev/null

# Clean vendor if exists
if [ -d "${SVN_DIR}/trunk/vendor" ]; then
    find "${SVN_DIR}/trunk/vendor" -type f -name "*.md" -delete 2>/dev/null || true
    find "${SVN_DIR}/trunk/vendor" -type d -name "tests" -exec rm -rf {} + 2>/dev/null || true
    find "${SVN_DIR}/trunk/vendor" -type d -name ".git" -exec rm -rf {} + 2>/dev/null || true
fi

# Remove composer files if no vendor
if [ ! -d "${SVN_DIR}/trunk/vendor" ]; then
    rm -f "${SVN_DIR}/trunk/composer.json"
    rm -f "${SVN_DIR}/trunk/composer.lock"
fi

# Add new files to SVN
echo -e "${YELLOW}Adding files to SVN...${NC}"
cd "${SVN_DIR}/trunk"
svn add --force . --auto-props --parents --depth infinity -q 2>/dev/null || true

# Remove deleted files from SVN
svn status | grep "^\!" | awk '{print $2}' | xargs -I {} svn rm {} 2>/dev/null || true
cd - > /dev/null

# Commit trunk
echo -e "${YELLOW}Committing trunk...${NC}"
cd "${SVN_DIR}"
svn commit -m "Update trunk to version ${VERSION}" \
    --username "${SVN_USERNAME}" \
    --password "${SVN_PASSWORD}" \
    --non-interactive \
    --trust-server-cert-failures=unknown-ca

# Create tag
echo -e "${YELLOW}Creating tag ${VERSION}...${NC}"
svn copy trunk "tags/${VERSION}"
svn commit -m "Tag version ${VERSION}" \
    --username "${SVN_USERNAME}" \
    --password "${SVN_PASSWORD}" \
    --non-interactive \
    --trust-server-cert-failures=unknown-ca

cd - > /dev/null

echo ""
echo -e "${GREEN}========================================================"
echo -e "Deployment complete!"
echo -e "========================================================${NC}"
echo ""
echo -e "Plugin URL: ${YELLOW}https://wordpress.org/plugins/${PLUGIN_SLUG}/${NC}"
echo -e "Version:    ${YELLOW}${VERSION}${NC}"
echo ""
echo -e "Note: It may take a few minutes for changes to appear on WordPress.org"
echo ""
