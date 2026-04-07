#!/bin/bash
# generate_images.sh — Génère les images loot via Gemini, slot par slot
# Usage :
#   bash generate_images.sh                  # tous les slots
#   bash generate_images.sh --force          # régénérer même si déjà fait
#   bash generate_images.sh --delay=6        # délai personnalisé
#   bash generate_images.sh --list           # voir l'état sans générer

PHP="/opt/cpanel/ea-php83/root/usr/bin/php"
ARTISAN="$HOME/donjon/artisan"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

SLOTS="arme armure casque bottes accessoire truc_bizarre"

[ ! -x "$PHP" ]     && echo -e "${RED}[ERREUR]${NC} PHP introuvable : $PHP" && exit 1
[ ! -f "$ARTISAN" ] && echo -e "${RED}[ERREUR]${NC} artisan introuvable : $ARTISAN" && exit 1

for slot in $SLOTS; do
    echo -e "${YELLOW}[SLOT]${NC} $slot"
    $PHP "$ARTISAN" images:generate --slot="$slot" "$@" || true
    echo ""
done

echo -e "${GREEN}[OK]${NC} Tous les slots traités."
