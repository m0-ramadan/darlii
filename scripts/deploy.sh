#!/usr/bin/env bash
# ==============================================================================
# Darlii Real Estate - Safe Production Deployment
# Target: darlii-prod (u802497710@82.25.83.24:65002)
# Remote path: domains/darlii.com/public_html/
#
# لا يلمس: .env / storage / uploads / vendor / node_modules / build
# ==============================================================================

set -euo pipefail

REMOTE_HOST="darlii-prod"
REMOTE_DIR="domains/darlii.com/public_html"
REMOTE_PHP="/opt/alt/php83/usr/bin/php"
REMOTE_COMPOSER="/usr/local/bin/composer"

# Determine project root safely
if git rev-parse --show-toplevel >/dev/null 2>&1; then
    PROJECT_ROOT="$(git rev-parse --show-toplevel)"
else
    SOURCE="${BASH_SOURCE[0]}"
    while [ -h "$SOURCE" ]; do
        DIR="$(cd -P "$(dirname "$SOURCE")" >/dev/null 2>&1 && pwd)"
        SOURCE="$(readlink "$SOURCE")"
        [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE"
    done
    PROJECT_ROOT="$(cd -P "$(dirname "$SOURCE")/.." >/dev/null 2>&1 && pwd)"
fi

echo "================================================================================"
echo "🚀 Darlii Real Estate -> Production Deployment ($REMOTE_HOST)"
echo "   Source: $PROJECT_ROOT"
echo "   Target: $REMOTE_HOST:$REMOTE_DIR/"
echo "================================================================================"

# 1. Verify SSH connection
echo "📡 [1/5] Verifying SSH connection to $REMOTE_HOST..."
if ! ssh -q -o BatchMode=yes -o ConnectTimeout=10 "$REMOTE_HOST" "exit"; then
    echo "❌ SSH connection failed to $REMOTE_HOST."
    echo "   Please check ~/.ssh/config for host entry 'darlii-prod'."
    exit 1
fi
echo "   ✓ SSH connection established."

# 2. Backup current remote code (بدون storage/uploads)
echo "🗄️  [2/5] Backing up current remote code..."
ssh "$REMOTE_HOST" "mkdir -p ~/deploy-backups && \
    tar czf ~/deploy-backups/code-\$(date +%Y%m%d-%H%M%S).tgz -C '$REMOTE_DIR' \
        app bootstrap config database lang resources routes scripts composer.json composer.lock artisan server.php 2>/dev/null || true && \
    ls -1t ~/deploy-backups/code-*.tgz 2>/dev/null | tail -n +11 | xargs -r rm -f"
echo "   ✓ Backup stored in ~/deploy-backups (last 10 kept)."

# 3. Sync application files safely
echo "📦 [3/5] Uploading updated application files..."
rsync -az --human-readable --chmod=D755,F644 \
    --exclude='.git/' \
    --exclude='.env*' \
    --exclude='node_modules/' \
    --exclude='vendor/' \
    --exclude='storage/' \
    --exclude='public/storage' \
    --exclude='public/hot' \
    --exclude='public/sitemap.xml' \
    --exclude='public/build' \
    --exclude='bootstrap/cache/*.php' \
    --exclude='.htaccess' \
    --exclude='.phpunit*' \
    --exclude='npm-debug.log*' \
    --exclude='yarn-error.log*' \
    --exclude='.idea/' \
    --exclude='.vscode/' \
    --exclude='.dsh/' \
    "$PROJECT_ROOT/app" \
    "$PROJECT_ROOT/bootstrap" \
    "$PROJECT_ROOT/config" \
    "$PROJECT_ROOT/database" \
    "$PROJECT_ROOT/lang" \
    "$PROJECT_ROOT/public" \
    "$PROJECT_ROOT/resources" \
    "$PROJECT_ROOT/routes" \
    "$PROJECT_ROOT/scripts" \
    "$PROJECT_ROOT/artisan" \
    "$PROJECT_ROOT/composer.json" \
    "$PROJECT_ROOT/composer.lock" \
    "$PROJECT_ROOT/server.php" \
    "$REMOTE_HOST:$REMOTE_DIR/"

echo "   ✓ Files synced (uploads & .env untouched)."

# 4. Rebuild caches, autoload, sitemap
echo "🧹 [4/5] Clearing caches, rebuilding autoload & sitemap on production..."
ssh "$REMOTE_HOST" "cd '$REMOTE_DIR' && \
    rm -f public/hot public/robots.txt && \
    find public -type f -perm 600 -exec chmod 644 {} + 2>/dev/null || true; \
    { [ -L public/storage ] || $REMOTE_PHP artisan storage:link; } && \
    { [ -f '$REMOTE_COMPOSER' ] && $REMOTE_PHP '$REMOTE_COMPOSER' dump-autoload -o --no-interaction || true; } && \
    $REMOTE_PHP artisan optimize:clear && \
    echo '--- pending migrations ---' && \
    ($REMOTE_PHP artisan migrate:status --no-ansi | tail -n 8 || true) && \
    $REMOTE_PHP artisan sitemap:generate && \
    $REMOTE_PHP artisan optimize && \
    $REMOTE_PHP artisan queue:restart"

echo "   ✓ Production cache & sitemap refreshed."

# 5. Health checks
echo "🌐 [5/5] Verifying live site..."
STATUS_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://darlii.com || echo "ERR")
ROBOTS_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://darlii.com/robots.txt || echo "ERR")
SITEMAP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://darlii.com/sitemap.xml || echo "ERR")
HOME_TITLE=$(curl -s https://darlii.com/ | grep -o '<title>[^<]*' | head -1 | sed 's/<title>//')

echo "   Home: HTTP $STATUS_CODE | robots: HTTP $ROBOTS_CODE | sitemap: HTTP $SITEMAP_CODE"
echo "   Title: $HOME_TITLE"

if [ "$STATUS_CODE" = "200" ]; then
    echo "   ✓ Site is healthy."
else
    echo "   ⚠️ Site returned HTTP $STATUS_CODE. Please verify in browser."
fi

# Firebase diagnostic (non-blocking)
echo "🔔 Checking Firebase credentials..."
ssh "$REMOTE_HOST" "cd '$REMOTE_DIR' && $REMOTE_PHP artisan firebase:check --no-ansi" || \
    echo "   ⚠️ Firebase credentials invalid — replace storage/app/firebase-service-account.json."

echo "================================================================================"
echo "🎉 Deployment finished successfully! https://darlii.com"
echo "================================================================================"
