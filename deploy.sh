#!/bin/bash
# Deploy Script — Cash Tracker (ADI CELL POS)
# Usage: bash deploy.sh

set -e

echo "=== Cash Tracker Deploy Script ==="
echo ""

# Check composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer not found. Install first."
    exit 1
fi

# 1. Install dependencies
echo "➡️  Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 2. Build assets
if command -v npm &> /dev/null; then
    echo "➡️  Building assets..."
    npm ci --production && npm run build
else
    echo "⚠️  npm not found, skipping asset build"
fi

# 3. Environment
if [ ! -f .env ]; then
    echo "➡️  Creating .env from .env.example..."
    cp .env.example .env
    echo "⚠️  Edit .env with your database & domain settings, then run:"
    echo "   php artisan key:generate"
    echo "   php artisan migrate --force"
fi

# 4. Storage link
echo "➡️  Creating storage link..."
php artisan storage:link --force 2>/dev/null || true

# 5. Optimize
echo "➡️  Caching..."
php artisan config:cache 2>/dev/null || echo "⚠️  config:cache skipped"
php artisan route:cache 2>/dev/null || echo "⚠️  route:cache skipped"
php artisan view:cache 2>/dev/null || echo "⚠️  view:cache skipped"

echo ""
echo "✅ Done! Edit .env and run:"
echo "   php artisan key:generate"
echo "   php artisan migrate --force"
