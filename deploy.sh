#!/bin/bash
# deploy.sh — Met à jour, build et déploie le projet complet
#
# Usage :
#   bash deploy.sh                  # mise à jour complète
#   bash deploy.sh --skip-images    # sans régénération des images IA
#   bash deploy.sh --skip-pull      # sans git pull (travail local)
#   bash deploy.sh --skip-build     # sans build frontend
#   bash deploy.sh --skip-laravel   # sans rsync Laravel

PHP="/opt/cpanel/ea-php83/root/usr/bin/php"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
FRONTEND_DIR="$SCRIPT_DIR/frontend"
WEBROOT="$HOME/donjon.techfg.dev"
LARAVEL_DIR="$HOME/donjon"

# ─── Couleurs ─────────────────────────────────────────────────────────────────
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

ok()   { echo -e "${GREEN}[OK]${NC} $1"; }
info() { echo -e "${YELLOW}[INFO]${NC} $1"; }
fail() { echo -e "${RED}[ERREUR]${NC} $1"; exit 1; }

# ─── Options ──────────────────────────────────────────────────────────────────
SKIP_PULL=false
SKIP_LARAVEL=false
SKIP_BUILD=false
SKIP_IMAGES=false

for arg in "$@"; do
    case $arg in
        --skip-pull)    SKIP_PULL=true ;;
        --skip-laravel) SKIP_LARAVEL=true ;;
        --skip-build)   SKIP_BUILD=true ;;
        --skip-images)  SKIP_IMAGES=true ;;
    esac
done

# ─── 1. Git pull ──────────────────────────────────────────────────────────────
if ! $SKIP_PULL; then
    info "Git pull..."
    cd "$SCRIPT_DIR"
    git pull || fail "git pull échoué"
    ok "Code à jour."
fi

# ─── 2. Rsync Laravel → donjon/ ───────────────────────────────────────────────
if ! $SKIP_LARAVEL; then
    info "Synchronisation Laravel → $LARAVEL_DIR..."
    [ ! -d "$LARAVEL_DIR" ] && fail "Dossier Laravel cible introuvable : $LARAVEL_DIR"
    rsync -avz --exclude='vendor/' --exclude='.env' --exclude='storage/logs/' \
        "$SCRIPT_DIR/laravel/" "$LARAVEL_DIR/" \
        > /dev/null
    ok "Laravel synchronisé."

    info "Migration DB..."
    $PHP "$LARAVEL_DIR/artisan" migrate --force
    ok "Migrations appliquées."

    info "Lien storage..."
    $PHP "$LARAVEL_DIR/artisan" storage:link --force 2>/dev/null || true
    # Symlink dans le webroot frontend → storage Laravel (pour /storage/heroes/... etc.)
    ln -sfn "$LARAVEL_DIR/storage/app/public" "$WEBROOT/storage"
    ok "Storage lié."
fi

# ─── 3. Build frontend ────────────────────────────────────────────────────────
if ! $SKIP_BUILD; then
    NODE="/opt/cpanel/ea-nodejs22/bin/node"
    NPM="/opt/cpanel/ea-nodejs22/bin/npm"

    [ ! -x "$NODE" ] && fail "Node.js introuvable : $NODE"
    [ ! -x "$NPM"  ] && fail "npm introuvable : $NPM"

    export PATH="/opt/cpanel/ea-nodejs22/bin:$PATH"
    ok "Node.js : $($NODE --version) | npm : $($NPM --version)"

    info "Installation des dépendances npm..."
    cd "$FRONTEND_DIR"
    $NPM install --silent

    info "Build React..."
    $NPM run build
    ok "Build terminé → $FRONTEND_DIR/dist/"

    info "Déploiement vers $WEBROOT..."
    [ ! -d "$WEBROOT" ] && fail "Webroot introuvable : $WEBROOT"
    cp -r "$FRONTEND_DIR/dist/." "$WEBROOT/"
    ok "Fichiers React copiés."

    # Gateway api/ → Laravel (répertoire réel, pas symlink)
    # Un symlink causerait SCRIPT_NAME=/api/index.php → Symfony strip /api → routes 404
    # Avec un vrai répertoire + gateway PHP qui override SCRIPT_NAME, Laravel reçoit le bon chemin
    [ -L "$WEBROOT/api" ] && rm "$WEBROOT/api"
    mkdir -p "$WEBROOT/api"
    cat > "$WEBROOT/api/index.php" << PHPEOF
<?php
// Override SCRIPT_NAME so Symfony/Laravel computes REQUEST_URI correctly:
// dirname('/index.php') = '' → pathInfo = /api/auth/login → matches api.php routes
\$_SERVER['SCRIPT_NAME'] = '/index.php';
\$_SERVER['PHP_SELF']    = '/index.php';
require '${LARAVEL_DIR}/public/index.php';
PHPEOF
    cat > "$WEBROOT/api/.htaccess" << 'HTEOF'
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
HTEOF
    ok "Gateway api/ créé → $LARAVEL_DIR/public."
fi

# ─── 4. Cache Laravel ─────────────────────────────────────────────────────────
if [ -f "$LARAVEL_DIR/artisan" ]; then
    info "Vidage du cache Laravel..."
    $PHP "$LARAVEL_DIR/artisan" config:cache
    $PHP "$LARAVEL_DIR/artisan" route:cache
    ok "Cache Laravel vidé."
fi

# ─── 5. Génération images IA ──────────────────────────────────────────────────
if ! $SKIP_IMAGES; then
    info "Génération des images manquantes via Gemini..."
    bash "$SCRIPT_DIR/generate_images.sh" --list
    echo ""
    read -p "Lancer la génération des images manquantes ? [o/N] " confirm
    if [[ "$confirm" =~ ^[oO]$ ]]; then
        bash "$SCRIPT_DIR/generate_images.sh"
    else
        ok "Génération d'images ignorée."
    fi
fi

echo ""
ok "Déploiement terminé."
