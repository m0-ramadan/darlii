#!/usr/bin/env bash
# ==============================================================================
# Darlii Real Estate - Automated Production Deployment Script
# Target: darlii-prod (u802497710@82.25.83.24:65002)
# Remote path: domains/darlii.com/public_html/
# ==============================================================================

set -euo pipefail

REMOTE_HOST="darlii-prod"
REMOTE_DIR="domains/darlii.com/public_html"
REMOTE_PHP="/opt/alt/php83/usr/bin/php"

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

# 1. Verify SSH Connection
echo "📡 [1/3] Verifying SSH connection to $REMOTE_HOST..."
if ! ssh -q -o BatchMode=yes -o ConnectTimeout=10 "$REMOTE_HOST" "exit"; then
    echo "❌ SSH connection failed to $REMOTE_HOST."
    echo "   Please check ~/.ssh/config for host entry 'darlii-prod'."
    exit 1
fi
echo "   ✓ SSH connection established."

# 2. Sync application files safely
echo "📦 [2/3] Uploading updated application files..."
rsync -avz \
    --exclude='.git/' \
    --exclude='.env*' \
    --exclude='node_modules/' \
    --exclude='vendor/' \
    --exclude='storage/' \
    --exclude='public/storage' \
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

echo "   ✓ Files synced successfully."

# 3. Clear remote cache & optimize
echo "🧹 [3/3] Clearing cache & warming up on production..."
ssh "$REMOTE_HOST" "cd $REMOTE_DIR && \
    $REMOTE_PHP artisan view:clear && \
    $REMOTE_PHP artisan route:clear && \
    $REMOTE_PHP artisan config:clear && \
    $REMOTE_PHP artisan cache:clear"

echo "🌐 Verifying live site (https://darlii.com)..."
STATUS_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://darlii.com || echo "ERR")
if [ "$STATUS_CODE" = "200" ]; then
    echo "   ✓ Site is healthy (HTTP $STATUS_CODE)"
else
    echo "   ⚠️ Site returned HTTP $STATUS_CODE. Please verify in browser."
fi

echo "================================================================================"
echo "🎉 Deployment finished successfully! https://darlii.com"
echo "================================================================================"
