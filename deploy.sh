#!/bin/bash
# deploy.sh — Build le frontend et déploie vers le web root
# Usage : bash deploy.sh

set -e

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

# ─── Node.js ──────────────────────────────────────────────────────────────────
NODE="/opt/cpanel/ea-nodejs22/bin/node"
NPM="/opt/cpanel/ea-nodejs22/bin/npm"

[ ! -x "$NODE" ] && fail "Node.js introuvable : $NODE"
[ ! -x "$NPM"  ] && fail "npm introuvable : $NPM"

# Ajouter node au PATH pour que tsc/vite (#!/usr/bin/env node) fonctionnent
export PATH="/opt/cpanel/ea-nodejs22/bin:$PATH"

ok "Node.js : $($NODE --version)"
ok "npm     : $($NPM --version)"

# ─── Build frontend ───────────────────────────────────────────────────────────
info "Installation des dépendances npm..."
cd "$FRONTEND_DIR"
$NPM install --silent

info "Build React..."
$NPM run build

ok "Build terminé → $FRONTEND_DIR/dist/"

# ─── Déploiement vers le webroot ──────────────────────────────────────────────
info "Déploiement vers $WEBROOT..."

[ ! -d "$WEBROOT" ] && fail "Dossier webroot introuvable : $WEBROOT"

# Copier les assets React (index.html, assets/)
cp -r "$FRONTEND_DIR/dist/." "$WEBROOT/"

ok "Fichiers React copiés."

# ─── Cache Laravel ────────────────────────────────────────────────────────────
if [ -f "$LARAVEL_DIR/artisan" ]; then
    info "Vidage du cache Laravel..."
    $PHP "$LARAVEL_DIR/artisan" config:cache
    $PHP "$LARAVEL_DIR/artisan" route:cache
    ok "Cache Laravel vidé."
fi

echo ""
ok "Déploiement terminé."
