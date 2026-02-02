#!/bin/bash
set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}🚀 Orange Absence Documentation Auto-Updater${NC}"

# Detect Docusaurus Directory
if [ -d "docs" ] && [ -f "docs/docusaurus.config.js" ]; then
    DOCS_DIR="docs"
elif [ -d "docusaurus" ] && [ -f "docusaurus/docusaurus.config.js" ]; then
    DOCS_DIR="docusaurus"
else
    echo -e "${RED}❌ Could not find Docusaurus directory (docs/ or docusaurus/)${NC}"
    exit 1
fi

echo -e "${GREEN}📂 Found Docusaurus in: $DOCS_DIR${NC}"

# 1. Update API Specs
echo -e "${YELLOW}🔄 Regenerating Swagger Specs...${NC}"
php artisan l5-swagger:generate

echo -e "${YELLOW}📋 Copying swagger.json...${NC}"
mkdir -p "$DOCS_DIR/static/api"
cp storage/api-docs/api-docs.json "$DOCS_DIR/static/api/swagger.json"

# 2. Build and Deploy
echo -e "${YELLOW}🏗️  Building and Deploying...${NC}"
cd "$DOCS_DIR"

# Install deps if missing
if [ ! -d "node_modules" ]; then
    npm ci
fi

# Build
npm run build

# Deploy to gh-pages
# Assumes SSH key is set up or prompt will appear
GIT_USER="${GIT_USER:-$(git config user.name)}" USE_SSH=true npm run deploy

echo -e "${GREEN}✅ Documentation updated and pushed to gh-pages!${NC}"
