# ⚔️ Système de Combat — Formules & Mécaniques

## 1. Table des constantes paramétrables (backend)

Toutes les valeurs marquées `[PARAM]` sont stockées dans une table `game_settings` en base de données (clé/valeur). Modifiables sans redéployer le code.

### 1.1 — Constantes générales

| Clé | Valeur par défaut | Description |
|-----|-------------------|-------------|
| `COMBAT_MAX_TURNS` | 15 | Nombre max de tours avant fin forcée (fuite mutuelle) |
| `LEVEL_SCALING_FACTOR` | 3 | Points de stats gagnés par niveau par stat principale |
| `LEVEL_SCALING_SECONDARY` | 1 | Points de stats gagnés par niveau par stat secondaire |
| `CRIT_BASE_CHANCE` | 5 | % de base de coup critique |
| `CRIT_DAMAGE_MULTIPLIER` | 150 | % des dégâts normaux en cas de critique (150 = ×1.5) |
| `DODGE_BASE_CHANCE` | 3 | % de base d'esquive |
| `DODGE_CAP` | 40 | % maximum d'esquive (plafond) |
| `CRIT_CAP` | 50 | % maximum de critique (plafond) |
| `DEF_SOFT_CAP` | 200 | Diviseur dans la formule de réduction de dégâts |
| `DEF_HARD_CAP` | 75 | % maximum de réduction de dégâts |
| `SPEED_BASE` | 100 | Vitesse de référence pour l'initiative |
| `OFFLINE_EFFICIENCY` | 75 | % d'efficacité du idle offline vs online |
| `OFFLINE_MAX_HOURS` | 12 | Heures max de calcul offline |
| `HEAL_BETWEEN_FIGHTS` | 30 | % de PV max récupérés entre chaque combat idle |
| `XP_BASE_PER_KILL` | 10 | XP de base par ennemi tué |
| `XP_LEVEL_MULTIPLIER` | 2 | Multiplicateur XP basé sur le niveau de l'ennemi |
| `XP_LEVEL_DIFF_PENALTY` | 5 | % de réduction d'XP par niveau d'écart (joueur > ennemi) |
| `XP_LEVEL_DIFF_BONUS` | 10 | % de bonus d'XP par niveau d'écart (ennemi > joueur) |
| `XP_TO_LEVEL_BASE` | 100 | XP requis pour le niveau 2 |
| `XP_TO_LEVEL_EXPONENT` | 115 | Facteur de croissance des niveaux en % (115 = ×1.15 par niveau) |

### 1.2 — Constantes de combat avancées

| Clé | Valeur par défaut | Description |
|-----|-------------------|-------------|
| `MIN_DAMAGE` | 1 | Dégâts minimum par attaque (même si DEF absorbe tout) |
| `VARIANCE_MIN` | 90 | % minimum de variance des dégâts |
| `VARIANCE_MAX` | 110 | % maximum de variance des dégâts |
| `MULTI_TARGET_PENALTY` | 70 | % des dégâts sur les cibles secondaires (AoE) |
| `FLEE_BASE_CHANCE` | 50 | % de chance de réussir une fuite (quêtes actives) |
| `DEATH_XP_PENALTY` | 0 | % d'XP perdu à la mort (0 = pas de perte) |
| `BOSS_STAT_MULTIPLIER` | 300 | % des stats d'un mob normal pour un boss de donjon |
| `WORLD_BOSS_HP_PER_PLAYER` | 5000 | PV ajoutés au boss mondial par joueur actif |

---

## 2. Stats de base des héros

### 2.1 — Les 6 stats

| Stat | Abréviation | Rôle |
|------|-------------|------|
| Points de Vie | PV | Santé. À 0 = K.O. |
| Attaque | ATQ | Dégâts infligés |
| Défense | DEF | Réduction des dégâts reçus |
| Vitesse | VIT | Détermine l'ordre d'initiative + esquive |
| Chance | CHA | Critique, loot, résistance aux effets |
| Intelligence | INT | Puissance des sorts, résistance magique |

### 2.2 — Stats de base par race (niveau 1, avant classe)

| Race | PV | ATQ | DEF | VIT | CHA | INT |
|------|-----|-----|-----|-----|-----|-----|
| Humain | 100 | 12 | 10 | 10 | 10 | 10 |
| Elfe | 80 | 14 | 8 | 14 | 12 | 14 |
| Nain | 120 | 14 | 14 | 6 | 8 | 6 |
| Gobelin | 70 | 10 | 6 | 16 | 14 | 8 |
| Orc | 140 | 16 | 8 | 8 | 6 | 4 |
| Demi-troll | 160 | 10 | 16 | 4 | 4 | 4 |

### 2.3 — Modificateurs de classe (ajoutés aux stats raciales)

Chaque classe a des **stats principales** (reçoivent `LEVEL_SCALING_FACTOR` par niveau) et des **stats secondaires** (reçoivent `LEVEL_SCALING_SECONDARY` par niveau).

| Classe | PV | ATQ | DEF | VIT | CHA | INT | Stats principales |
|--------|-----|-----|-----|-----|-----|-----|-------------------|
| Guerrier | +30 | +5 | +5 | +0 | +0 | +0 | PV, ATQ, DEF |
| Mage | +0 | +3 | +0 | +0 | +0 | +8 | INT, ATQ |
| Voleur | +0 | +4 | +0 | +6 | +4 | +0 | VIT, CHA |
| Ranger | +10 | +5 | +2 | +4 | +2 | +0 | ATQ, VIT |
| Prêtre | +20 | +0 | +3 | +0 | +2 | +5 | INT, PV |
| Barde | +10 | +0 | +0 | +3 | +6 | +3 | CHA, INT |
| Barbare | +20 | +8 | +0 | +2 | +0 | +0 | ATQ, PV |
| Nécromancien | +0 | +2 | +0 | +0 | +0 | +8 | INT, CHA |

### 2.4 — Formule de stats à un niveau donné

```
Stat_finale = Stat_raciale + Modificateur_classe + Bonus_niveau + Bonus_équipement

Où Bonus_niveau :
  - Si stat principale : (Niveau - 1) × LEVEL_SCALING_FACTOR
  - Si stat secondaire : (Niveau - 1) × LEVEL_SCALING_SECONDARY

Exemple : Nain Guerrier niveau 20
  PV = 120 (race) + 30 (classe) + (19 × 3) (principal) = 207
  ATQ = 14 (race) + 5 (classe) + (19 × 3) (principal) = 76
  DEF = 14 (race) + 5 (classe) + (19 × 3) (principal) = 76
  VIT = 6 (race) + 0 (classe) + (19 × 1) (secondaire) = 25
  CHA = 8 (race) + 0 (classe) + (19 × 1) (secondaire) = 27
  INT = 6 (race) + 0 (classe) + (19 × 1) (secondaire) = 25
```

> **Règle :** Tous les résultats sont arrondis à l'entier inférieur (floor). Jamais de décimales.

---

## 3. Formules de combat

### 3.1 — Initiative (ordre des tours)

Détermine qui agit en premier à chaque tour.

```
Score_initiative = VIT + random(1, 20)
```

Les combattants sont triés par score décroissant. Recalculé à chaque tour pour introduire de la variabilité.

### 3.2 — Jet d'attaque (touche ou esquive)

```
Chance_esquive = min(
  DEF_cible × 100 / (DEF_cible + VIT_attaquant + SPEED_BASE),
  DODGE_CAP
)

Jet = random(1, 100)
Si Jet <= Chance_esquive → Esquive (0 dégâts, message du Narrateur)
```

Exemple : DEF 50, VIT attaquant 30, SPEED_BASE 100
→ Chance = min(50 × 100 / (50 + 30 + 100), 40) = min(27, 40) = 27%

### 3.3 — Calcul des dégâts

```
Dégâts_bruts = ATQ_attaquant × random(VARIANCE_MIN, VARIANCE_MAX) / 100

Réduction = min(DEF_cible × 100 / (DEF_cible + DEF_SOFT_CAP), DEF_HARD_CAP)

Dégâts_nets = max(Dégâts_bruts × (100 - Réduction) / 100, MIN_DAMAGE)
```

Tout est calculé en entiers avec division entière (floor).

**Exemple concret :**
```
ATQ = 76, DEF cible = 40, DEF_SOFT_CAP = 200

Dégâts_bruts = 76 × 103 / 100 = 78 (variance aléatoire 103%)
Réduction = min(40 × 100 / (40 + 200), 75) = min(16, 75) = 16%
Dégâts_nets = max(78 × (100 - 16) / 100, 1) = max(78 × 84 / 100, 1) = max(65, 1) = 65
```

### 3.4 — Coups critiques

```
Chance_critique = min(CRIT_BASE_CHANCE + CHA / 4, CRIT_CAP)

Jet = random(1, 100)
Si Jet <= Chance_critique → Critique !

Dégâts_critiques = Dégâts_nets × CRIT_DAMAGE_MULTIPLIER / 100
```

Exemple : CHA = 28
→ Chance = min(5 + 28/4, 50) = min(5 + 7, 50) = 12%

### 3.5 — Dégâts magiques

Les sorts utilisent INT au lieu d'ATQ, et ignorent une partie de la DEF :

```
Dégâts_bruts_magiques = INT_attaquant × random(VARIANCE_MIN, VARIANCE_MAX) / 100

Résistance_magique = min(INT_cible × 100 / (INT_cible + DEF_SOFT_CAP), DEF_HARD_CAP)

Dégâts_nets_magiques = max(Dégâts_bruts_magiques × (100 - Résistance_magique / 2) / 100, MIN_DAMAGE)
```

> La résistance magique est divisée par 2 — les sorts pénètrent mieux que les attaques physiques. Ça rend le Mage fort contre les tanks mais vulnérable en retour (peu de PV/DEF).

### 3.6 — Soins (Prêtre)

```
Soin_base = INT_prêtre × random(VARIANCE_MIN, VARIANCE_MAX) / 100
Soin_effectif = min(Soin_base, PV_max_cible - PV_actuel_cible)
```

Le soin ne peut pas dépasser les PV max. Pas de sur-soin.

---

## 4. Déroulement d'un combat

### 4.1 — Combat idle (PvE automatique)

```
DÉBUT DU COMBAT
  Générer le groupe ennemi (basé sur la zone et le niveau)
  
  POUR chaque tour (1 à COMBAT_MAX_TURNS) :
    
    1. Calculer l'initiative de tous les combattants vivants
    2. Trier par initiative décroissante
    
    3. POUR chaque combattant (dans l'ordre d'initiative) :
       
       a. Vérifier les effets de statut :
          - Étourdi → skip le tour
          - Endormi → skip le tour (se réveille si touché)
          - En fuite → retiré du combat
       
       b. Vérifier le déclenchement du trait négatif :
          - Jet contre le % de déclenchement du trait
          - Si déclenché → appliquer l'effet (voir doc Traits)
          - Le Narrateur commente
       
       c. Choisir l'action :
          - IA basique : attaque le plus faible en PV (ou soin sur l'allié le plus faible)
          - Si compétence disponible (cooldown écoulé) → utiliser la compétence
          - Sinon → attaque de base
       
       d. Résoudre l'action :
          - Jet d'esquive
          - Calcul des dégâts / soins
          - Appliquer les effets spéciaux (loot, compétences)
       
       e. Vérifier les morts :
          - PV <= 0 → K.O.
          - Si tous les ennemis K.O. → VICTOIRE
          - Si tous les héros K.O. → DÉFAITE
    
    4. Si tour = COMBAT_MAX_TURNS → MATCH NUL (fuite mutuelle)

FIN DU COMBAT
  VICTOIRE : XP + loot + commentaire Narrateur
  DÉFAITE : pas de pénalité, commentaire moqueur du Narrateur
  MATCH NUL : XP partiel (50%), pas de loot
```

### 4.2 — Combat actif (quêtes à embranchements)

Même système, mais le joueur peut à chaque tour de ses héros :
- **Choisir la cible** (au lieu de l'IA basique)
- **Forcer une compétence** (même si l'IA aurait attaqué normalement)
- **Utiliser un consommable** (potion, parchemin — ne consomme pas le tour)
- **Fuir** (jet contre `FLEE_BASE_CHANCE`, si raté le héros perd son tour)

---

## 5. XP et montée de niveau

### 5.1 — XP gagnée par combat

```
XP_par_ennemi = XP_BASE_PER_KILL + (Niveau_ennemi × XP_LEVEL_MULTIPLIER)

Diff_niveau = Niveau_ennemi - Niveau_héros

Si Diff_niveau > 0 (ennemi plus fort) :
  Bonus = Diff_niveau × XP_LEVEL_DIFF_BONUS
  XP_finale = XP_par_ennemi × (100 + Bonus) / 100

Si Diff_niveau < 0 (ennemi plus faible) :
  Malus = abs(Diff_niveau) × XP_LEVEL_DIFF_PENALTY
  XP_finale = max(XP_par_ennemi × (100 - Malus) / 100, 1)

Si Diff_niveau = 0 :
  XP_finale = XP_par_ennemi
```

L'XP est répartie **également** entre les héros vivants à la fin du combat.

### 5.2 — XP requise par niveau

```
XP_requise(N) = XP_TO_LEVEL_BASE × (XP_TO_LEVEL_EXPONENT ^ (N - 1)) / (100 ^ (N - 1))
```

En pratique avec les valeurs par défaut (base 100, exposant 115) :

| Niveau | XP requise | Cumul |
|--------|------------|-------|
| 2 | 100 | 100 |
| 3 | 115 | 215 |
| 5 | 152 | 549 |
| 10 | 304 | 1,783 |
| 20 | 1,232 | 11,973 |
| 30 | 4,997 | 60,637 |
| 50 | 82,167 | 648,439 |
| 75 | 4,271,484 | 31,296,203 |
| 100 | 221,924,532 | 1,595,023,810 |

> La courbe est exponentielle — les derniers niveaux sont un grind volontaire pour les joueurs hardcore. Ajustable via `XP_TO_LEVEL_EXPONENT`.

---

## 6. Calcul idle offline

### 6.1 — À la reconnexion

```
Temps_écoulé = min(maintenant - dernière_connexion, OFFLINE_MAX_HOURS × 3600)

Combats_simulés = Temps_écoulé / Durée_moyenne_combat_zone

POUR chaque combat simulé :
  Résultat = simulation simplifiée (stats équipe vs stats mob moyen de la zone)
  
  Si victoire (probabilité basée sur le ratio de puissance) :
    XP += XP_mob × OFFLINE_EFFICIENCY / 100
    Loot += jet de loot (même table, même probabilités)
  
  Si défaite :
    Arrêt de la simulation (l'équipe est K.O.)
    PV de l'équipe = 1 chacun (ils se sont traînés à l'auberge)
```

### 6.2 — Ratio de puissance (pour simulation offline)

```
Puissance_héros = (ATQ + INT) × PV × (100 + DEF) / 100
Puissance_équipe = somme des puissances individuelles

Puissance_mob = même formule pour le groupe ennemi

Ratio = Puissance_équipe × 100 / Puissance_mob

Si Ratio >= 150 → Victoire auto (95%)
Si Ratio >= 100 → Victoire probable (75%)
Si Ratio >= 70  → Combat serré (50%)
Si Ratio >= 50  → Défaite probable (25%)
Si Ratio < 50   → Défaite quasi certaine (5%)
```

---

## 7. Effets de statut

Les compétences et traits peuvent infliger des effets de statut :

| Effet | Durée (tours) | Mécanisme |
|-------|---------------|-----------|
| Étourdi | 1 | Skip le prochain tour |
| Endormi | 1-3 | Skip les tours. Se réveille si touché (50% de chance) |
| En feu | 2-4 | Perd 5% de PV max par tour (dégâts de zone Pyromane) |
| Empoisonné | 3-5 | Perd 3% de PV max par tour |
| Ralenti | 2-3 | VIT divisée par 2 pour l'initiative |
| Inspiré | 2-3 | ATQ et INT +20% (buff Barde) |
| Protégé | 1-2 | DEF +30% (buff Prêtre/Guerrier) |
| Régénération | 3 | Récupère 5% de PV max par tour |
| Terrifié | 1-2 | 30% de chance de skip le tour (peur) |
| Paperasserie | 1 | Skip le tour (spécial loot Bureaucratie) |

> Les % dans les effets de statut (5% de PV max, etc.) sont calculés en entiers : `PV_max × 5 / 100` arrondi floor. Un héros avec 207 PV max perd 10 PV/tour en feu.

---

## 8. Génération des ennemis

### 8.1 — Stats des ennemis

```
PV_ennemi = PV_base_zone × (100 + Niveau_ennemi × 5) / 100
ATQ_ennemi = ATQ_base_zone × (100 + Niveau_ennemi × 4) / 100
DEF_ennemi = DEF_base_zone × (100 + Niveau_ennemi × 3) / 100
```

Chaque zone a des stats de base pour ses mobs, définies en base de données.

### 8.2 — Groupes ennemis

| Zone niveau | Mobs par combat | Boss de donjon |
|-------------|-----------------|----------------|
| 1-10 | 1-2 | Stats × BOSS_STAT_MULTIPLIER / 100 |
| 11-25 | 2-3 | Stats × BOSS_STAT_MULTIPLIER / 100 |
| 26-50 | 2-4 | Stats × BOSS_STAT_MULTIPLIER / 100 |
| 51-75 | 3-4 | Stats × BOSS_STAT_MULTIPLIER / 100 |
| 76-100 | 3-5 | Stats × BOSS_STAT_MULTIPLIER / 100 |

---

## 9. Boss mondiaux — Combat spécial

```
PV_boss_mondial = WORLD_BOSS_HP_PER_PLAYER × nombre_joueurs_actifs

Dégâts_joueur = résultat d'un combat normal contre le boss (1 tentative)
  → Les dégâts totaux infligés par l'équipe sont enregistrés

Le boss ne meurt pas pendant le combat individuel.
Les dégâts de tous les joueurs sont cumulés sur le pool de PV.

Quand PV_boss_mondial <= 0 → Boss vaincu
  → Classement des joueurs par dégâts totaux infligés
  → Récompenses selon le classement (voir GDD section 4.9)
```

### 9.1 — Mécaniques spéciales de boss

Chaque boss mondial a 1-2 mécaniques uniques définies en base de données :

```json
{
  "boss_id": 1,
  "name": "Le Kraken Comptable",
  "mechanic_1": {
    "type": "stat_swap",
    "description": "Échange ATQ et DEF d'un héros aléatoire chaque tour",
    "trigger": "every_turn",
    "chance": 30
  },
  "mechanic_2": {
    "type": "audit",
    "description": "Réduit l'or gagné de 50% pour le reste du combat",
    "trigger": "hp_threshold",
    "threshold": 50,
    "chance": 100
  }
}
```

---

## 10. Résumé des formules clés

| Formule | Expression |
|---------|------------|
| **Initiative** | `VIT + random(1, 20)` |
| **Esquive** | `min(DEF × 100 / (DEF + VIT_atk + SPEED_BASE), DODGE_CAP)` |
| **Dégâts physiques** | `max(ATQ × variance / 100 × (100 - réduction) / 100, MIN_DAMAGE)` |
| **Réduction DEF** | `min(DEF × 100 / (DEF + DEF_SOFT_CAP), DEF_HARD_CAP)` |
| **Dégâts magiques** | `max(INT × variance / 100 × (100 - résistance/2) / 100, MIN_DAMAGE)` |
| **Critique chance** | `min(CRIT_BASE_CHANCE + CHA / 4, CRIT_CAP)` |
| **Critique dégâts** | `Dégâts × CRIT_DAMAGE_MULTIPLIER / 100` |
| **XP par kill** | `XP_BASE + (Niv_ennemi × XP_LEVEL_MULT) ± ajustement niveau` |
| **XP pour level** | `XP_BASE × (EXPONENT ^ (N-1)) / (100 ^ (N-1))` |
| **Puissance** | `(ATQ + INT) × PV × (100 + DEF) / 100` |
