#!/bin/bash
# generate_images.sh — Génère les images loot via Gemini
# Usage :
#   bash generate_images.sh                        # tous les slots, toutes les raretés
#   bash generate_images.sh --list                 # voir l'état sans générer
#   bash generate_images.sh --slot=arme            # un slot spécifique
#   bash generate_images.sh --rarity=legendaire    # une rareté spécifique
#   bash generate_images.sh --slot=arme --rarity=epique --force
#   bash generate_images.sh --delay=6              # délai personnalisé

PHP="/opt/cpanel/ea-php83/root/usr/bin/php"
ARTISAN="$HOME/donjon/artisan"

GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

[ ! -x "$PHP" ]     && echo -e "${RED}[ERREUR]${NC} PHP introuvable : $PHP" && exit 1
[ ! -f "$ARTISAN" ] && echo -e "${RED}[ERREUR]${NC} artisan introuvable : $ARTISAN" && exit 1

echo -e "${GREEN}[INFO]${NC} Lancement de images:generate $@"
$PHP "$ARTISAN" images:generate "$@"
