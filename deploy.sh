#!/bin/bash

# 🍊 Orange Absence - cPanel Deployment Script
# Compatible with shared hosting environments
# Version: 2.0 (cPanel Optimized)

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}  🍊 ORANGE ABSENCE DEPLOYMENT${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# 0. Emergency Cache Nuke (Run first to prevent boot errors)
echo -e "${YELLOW}🧹 Pre-flight: Nuking bootstrap cache...${NC}"
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/filament_components.php
rm -f bootstrap/cache/livewire-components.php

# Detect if this is first-time setup
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚙️  First-time setup detected!${NC}"
    FIRST_SETUP=true
else
    echo -e "${GREEN}🔄 Update deployment mode${NC}"
    FIRST_SETUP=false
fi

# 1. Environment Setup (First Time Only)
if [ "$FIRST_SETUP" = true ]; then
    echo -e "${YELLOW}📝 Creating .env file...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✓ .env file created${NC}"
    echo ""
    echo -e "${YELLOW}⚠️  IMPORTANT: Please edit .env file and configure:${NC}"
    echo "   - DB_DATABASE, DB_USERNAME, DB_PASSWORD"
    echo "   - APP_URL"
    echo "   - QUEUE_CONNECTION (use 'database' for cPanel)"
    echo ""
    read -p "Press Enter after you've configured .env file..."
fi

# 2. Check PHP version
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo -e "${GREEN}🐘 PHP Version: $PHP_VERSION${NC}"

if php -r "exit(version_compare('$PHP_VERSION', '8.2', '<') ? 0 : 1);"; then
    echo -e "${RED}❌ PHP 8.2 or higher required!${NC}"
    echo "   Please change PHP version in cPanel"
    exit 1
fi

# 3. Maintenance Mode
if [ "$FIRST_SETUP" = false ]; then
    echo -e "${YELLOW}🔒 Entering maintenance mode...${NC}"
    php artisan down --retry=60 || true
fi

# 4. Update Dependencies (Force Update for Version Fix)
echo -e "${GREEN}📦 Updating Composer dependencies...${NC}"

# Nuke bootstrap cache first to prevent "class_exists(null)" errors during package discovery
echo -e "${YELLOW}🧹 Nuking bootstrap cache manually...${NC}"
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php

if command -v composer &> /dev/null; then
    # We use update instead of install to ensure lock file syncs with new json
    composer update --no-dev --optimize-autoloader --no-interaction
else
    echo -e "${YELLOW}⚠️  Using php composer.phar...${NC}"
    php composer.phar update --no-dev --optimize-autoloader --no-interaction
fi

# 5. Generate App Key (First Time Only)
if [ "$FIRST_SETUP" = true ]; then
    echo -e "${GREEN}🔑 Generating application key...${NC}"
    php artisan key:generate --force
fi

# 6. Install Node Dependencies & Build Assets
echo -e "${GREEN}🎨 Building frontend assets...${NC}"
if command -v npm &> /dev/null; then
    npm install --production
    npm run build
else
    echo -e "${YELLOW}⚠️  npm not found, skipping asset build${NC}"
    echo "   Please build assets manually: npm install && npm run build"
fi

# 7. Database Setup
if [ "$FIRST_SETUP" = true ]; then
    echo -e "${GREEN}💾 Running database migrations with seed data...${NC}"
    php artisan migrate:fresh --seed --force
else
    echo -e "${GREEN}💾 Running database migrations...${NC}"
    php artisan migrate --force
fi

# 8. Storage Link
echo -e "${GREEN}🔗 Creating storage symlink...${NC}"
php artisan storage:link --force || true

# 9. File Permissions (cPanel Safe)
echo -e "${GREEN}📝 Setting file permissions...${NC}"
chmod -R 755 storage bootstrap/cache
find storage -type f -exec chmod 644 {} \;
find storage -type d -exec chmod 755 {} \;

# 10. Production Optimization & Recovery
echo -e "${GREEN}⚡ Optimizing for production...${NC}"
# Clear everything first to fix "ComponentNotFound" errors
php artisan optimize:clear
php artisan filament:optimize-clear
php artisan view:clear

# Re-cache everything safely
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache 2>/dev/null || true
php artisan filament:assets 2>/dev/null || true

# 10.1. Generates API Documentation (Swagger)
echo -e "${GREEN}📖 Generating API Documentation...${NC}"
if php artisan l5-swagger:generate; then
    echo -e "${GREEN}✓ Swagger docs generated${NC}"
    
    # Sync with Docusaurus (detect folder)
    if [ -d "docs/static/api" ]; then
        DOCS_API_DIR="docs/static/api"
    elif [ -d "docusaurus/static/api" ]; then
        DOCS_API_DIR="docusaurus/static/api"
    fi

    if [ ! -z "$DOCS_API_DIR" ]; then
        echo -e "${GREEN}🔄 Syncing swagger.json to $DOCS_API_DIR...${NC}"
        mkdir -p "$DOCS_API_DIR"
        cp storage/api-docs/api-docs.json "$DOCS_API_DIR/swagger.json"
    else
        echo -e "${YELLOW}⚠️  Docusaurus API folder not found (skipped sync)${NC}"
    fi
else
    echo -e "${RED}❌ Failed to generate Swagger docs${NC}"
    # Don't exit, just warn
fi

# 11. Symlink Self-Correction (Critical for cPanel)
echo -e "${GREEN}🔗 Fixing storage symlink (Absolute Path Fix)...${NC}"
rm -rf public/storage
# Get absolute path dynamically
USER_HOME=$(eval echo "~")
ln -s "$USER_HOME/orange-absence/storage/app/public" "$(pwd)/public/storage" 2>/dev/null || php artisan storage:link --force

# 12. Queue Table & Final Sweep
echo -e "${GREEN}🧹 Final cleanup...${NC}"
php artisan cache:clear
php artisan migrate --force

# 13. Test Database Connection
echo -e "${GREEN}🔌 Testing database connection...${NC}"
php artisan db:show 2>/dev/null || echo "Database connected ✓"

# 14. Exit Maintenance Mode
if [ "$FIRST_SETUP" = false ]; then
    echo -e "${GREEN}🔓 Exiting maintenance mode...${NC}"
    php artisan up
fi

# 15. Display Post-Deployment Instructions
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}  ✅ DEPLOYMENT COMPLETED SUCCESSFULLY!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

if [ "$FIRST_SETUP" = true ]; then
    echo -e "${YELLOW}📌 NEXT STEPS FOR CPANEL:${NC}"
    echo ""
    echo "1️⃣  Setup Cron Jobs in cPanel:"
    echo "   ┌─────────────────────────────────────────────"
    echo "   │ Minute:  *"
    echo "   │ Hour:    *"
    echo "   │ Day:     *"
    echo "   │ Month:   *"
    echo "   │ Weekday: *"
    echo "   │ Command: cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
    echo "   └─────────────────────────────────────────────"
    echo ""
    echo "2️⃣  Setup Queue Processing (IMPORTANT FOR NOTIFICATIONS!):"
    echo "   Add this cron job (runs every minute):"
    echo "   ┌─────────────────────────────────────────────"
    echo "   │ * * * * * cd $(pwd) && php artisan queue:work --stop-when-empty >> /dev/null 2>&1"
    echo "   └─────────────────────────────────────────────"
    echo ""
    echo "3️⃣  Default Login Credentials:"
    echo "   ┌─────────────────────────────────────────────"
    echo "   │ Super Admin:"
    echo "   │   Email: admin@orange.test"
    echo "   │   Pass:  password"
    echo "   │"
    echo "   │ Secretary (Game Division):"
    echo "   │   Email: secretary@orange.test"
    echo "   │   Pass:  password"
    echo "   │"
    echo "   │ Member:"
    echo "   │   Email: member@orange.test"
    echo "   │   Pass:  password"
    echo "   └─────────────────────────────────────────────"
    echo ""
    echo "4️⃣  Access your application:"
    echo "   🌐 Frontend: $(grep APP_URL .env | cut -d '=' -f2)"
    echo "   🔐 Admin:    $(grep APP_URL .env | cut -d '=' -f2)/admin"
    echo "   👤 Member:   $(grep APP_URL .env | cut -d '=' -f2)/member"
    echo ""
else
    echo -e "${GREEN}🎉 Application updated successfully!${NC}"
    echo ""
    echo -e "${YELLOW}💡 TIP: Clear browser cache if you see issues${NC}"
fi

echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}Documentation: README.md | CPANEL_DEPLOYMENT.md${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"