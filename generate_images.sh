#!/bin/bash
# generate_images.sh — Génère toutes les images du jeu via Gemini
#
# Usage :
#   bash generate_images.sh                   # tout générer (loot + héros + zones + monstres)
#   bash generate_images.sh --loot            # items loot uniquement
#   bash generate_images.sh --heroes          # portraits héros uniquement
#   bash generate_images.sh --zones           # backgrounds zones uniquement
#   bash generate_images.sh --monsters        # monstres base + élite uniquement
#   bash generate_images.sh --monsters-elite  # élites uniquement (utilise image de base)
#   bash generate_images.sh --list            # voir l'état sans générer
#   bash generate_images.sh --force           # régénérer tout
#   bash generate_images.sh --delay=6         # délai personnalisé

PHP="/opt/cpanel/ea-php83/root/usr/bin/php"
ARTISAN="$HOME/donjon/artisan"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

SLOTS="arme armure casque bottes accessoire truc_bizarre"

[ ! -x "$PHP" ]     && echo -e "${RED}[ERREUR]${NC} PHP introuvable : $PHP" && exit 1
[ ! -f "$ARTISAN" ] && echo -e "${RED}[ERREUR]${NC} artisan introuvable : $ARTISAN" && exit 1

# ── Parse options ─────────────────────────────────────────────────────────────
DO_LOOT=false DO_HEROES=false DO_ZONES=false DO_MONSTERS=false DO_ELITE_ONLY=false
LIST=false FORCE="" DELAY=""

for arg in "$@"; do
    case $arg in
        --loot)           DO_LOOT=true ;;
        --heroes)         DO_HEROES=true ;;
        --zones)          DO_ZONES=true ;;
        --monsters)       DO_MONSTERS=true ;;
        --monsters-elite) DO_MONSTERS=true; DO_ELITE_ONLY=true ;;
        --list)           LIST=true ;;
        --force)          FORCE="--force" ;;
        --delay=*)        DELAY="$arg" ;;
    esac
done

# Si aucune cible spécifiée → tout faire
if ! $DO_LOOT && ! $DO_HEROES && ! $DO_ZONES && ! $DO_MONSTERS; then
    DO_LOOT=true; DO_HEROES=true; DO_ZONES=true; DO_MONSTERS=true
fi

LIST_FLAG=$($LIST && echo "--list" || echo "")

# ── Loot images ───────────────────────────────────────────────────────────────
if $DO_LOOT; then
    echo -e "\n${YELLOW}══ LOOT IMAGES ══${NC}"
    for slot in $SLOTS; do
        echo -e "${YELLOW}[slot]${NC} $slot"
        $PHP "$ARTISAN" images:generate --slot="$slot" $FORCE $DELAY $LIST_FLAG || true
        echo ""
    done
fi

# ── Héros portraits ───────────────────────────────────────────────────────────
if $DO_HEROES; then
    echo -e "\n${YELLOW}══ PORTRAITS HÉROS ══${NC}"
    $PHP "$ARTISAN" images:heroes $FORCE $DELAY $LIST_FLAG || true
fi

# ── Zone backgrounds ──────────────────────────────────────────────────────────
if $DO_ZONES; then
    echo -e "\n${YELLOW}══ BACKGROUNDS ZONES ══${NC}"
    $PHP "$ARTISAN" images:zones $FORCE $DELAY $LIST_FLAG || true
fi

# ── Monstres ──────────────────────────────────────────────────────────────────
if $DO_MONSTERS; then
    echo -e "\n${YELLOW}══ IMAGES MONSTRES ══${NC}"
    ELITE_FLAG=$($DO_ELITE_ONLY && echo "--elite" || echo "--all")
    $PHP "$ARTISAN" images:monsters $ELITE_FLAG $FORCE $DELAY $LIST_FLAG || true
fi

echo -e "\n${GREEN}[OK]${NC} Génération terminée."
