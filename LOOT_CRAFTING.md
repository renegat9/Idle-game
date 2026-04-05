# 🎒 Système de Loot & Crafting — Mécaniques Détaillées

## 1. Constantes paramétrables (table `game_settings`)

### 1.1 — Loot

| Clé | Valeur par défaut | Description |
|-----|-------------------|-------------|
| `LOOT_DROP_CHANCE` | 60 | % de chance de drop après un combat |
| `LOOT_RARITY_COMMUN` | 50 | % de chance Commun (si loot) |
| `LOOT_RARITY_PEU_COMMUN` | 25 | % Peu commun |
| `LOOT_RARITY_RARE` | 14 | % Rare |
| `LOOT_RARITY_EPIQUE` | 7 | % Épique |
| `LOOT_RARITY_LEGENDAIRE` | 3 | % Légendaire |
| `LOOT_RARITY_WTF` | 1 | % WTF |
| `LOOT_LEVEL_RANGE` | 3 | L'objet drop dans une fourchette de ±X niveaux de la zone |
| `LOOT_STAT_VARIANCE` | 15 | % de variance sur les stats générées |
| `LOOT_DURABILITY_BASE` | 100 | Durabilité de base (tous sauf WTF) |
| `LOOT_DURABILITY_WTF` | 30 | Durabilité des objets WTF (cassent vite) |
| `LOOT_DURABILITY_LOSS_PER_COMBAT` | 1 | Durabilité perdue par combat |
| `LOOT_REPAIR_COST_MULTIPLIER` | 2 | Multiplicateur du coût de réparation par point de durabilité |
| `LOOT_SELL_PERCENT` | 30 | % de la valeur de base récupéré à la vente |
| `LOOT_AI_GENERATION_MIN_RARITY` | 3 | Rareté minimum pour déclencher Gemini (3 = Rare) |

### 1.2 — Crafting

| Clé | Valeur par défaut | Description |
|-----|-------------------|-------------|
| `CRAFT_FUSION_COUNT` | 3 | Nombre d'objets requis pour une fusion |
| `CRAFT_FUSION_SUCCESS` | 85 | % de chance de succès |
| `CRAFT_FUSION_CRIT` | 10 | % de chance de critique (résultat +1 rareté) |
| `CRAFT_DISMANTLE_MATERIAL_MIN` | 1 | Matériaux minimum par démontage |
| `CRAFT_DISMANTLE_MATERIAL_MAX` | 5 | Matériaux maximum par démontage |
| `CRAFT_RECIPE_DISCOVER_CHANCE` | 5 | % de chance de découvrir une recette en craftant |
| `CRAFT_GERARD_HUMOR_CHANCE` | 30 | % de chance que Gérard fasse un commentaire |

---

## 2. Slots d'équipement

Chaque héros possède **6 slots** :

| Slot | Stats principales possibles | Exemples |
|------|----------------------------|----------|
| Arme | ATQ, INT, CHA | Épée, Bâton, Arc, Dague, Luth |
| Armure | DEF, PV | Cotte de mailles, Robe, Cuir |
| Casque | DEF, INT, CHA | Heaume, Capuche, Chapeau pointu |
| Bottes | VIT, DEF | Bottes lourdes, Sandales elfiques |
| Accessoire | Toutes stats | Anneau, Amulette, Cape |
| Truc Bizarre | Effet spécial uniquement | Réservé aux objets WTF |

### Restrictions de classe

| Classe | Armes autorisées | Armures autorisées |
|--------|------------------|-------------------|
| Guerrier | Épées, Haches, Masses | Lourdes, Moyennes |
| Mage | Bâtons, Orbes | Robes, Légères |
| Voleur | Dagues, Épées courtes | Légères, Moyennes |
| Ranger | Arcs, Arbalètes | Légères, Moyennes |
| Prêtre | Masses, Sceptres | Moyennes, Robes |
| Barde | Luths, Flûtes, Épées courtes | Légères, Moyennes |
| Barbare | Haches, Masses, Épées à deux mains | Légères (ou rien) |
| Nécromancien | Bâtons, Orbes, Faux | Robes, Légères |

> Le slot **Truc Bizarre** n'a aucune restriction de classe. Tout le monde peut porter un "Chapeau en Fromage du Destin".

---

## 3. Génération des objets

### 3.1 — Processus de drop

```
Après un combat victorieux :

1. Jet de drop : random(1, 100) <= LOOT_DROP_CHANCE ?
   Non → pas de loot
   Oui → continuer

2. Jet de rareté : random(1, 100)
   1-50   → Commun
   51-75  → Peu commun
   76-89  → Rare
   90-96  → Épique
   97-99  → Légendaire
   100    → WTF

3. Déterminer le slot : random parmi les 6 slots
   (pondéré : Arme 25%, Armure 20%, Casque 15%, Bottes 15%,
    Accessoire 15%, Truc Bizarre 10%)

4. Déterminer le niveau de l'objet :
   Niveau_objet = Niveau_zone + random(-LOOT_LEVEL_RANGE, LOOT_LEVEL_RANGE)
   Minimum : 1

5. Générer les stats (voir section 3.2)

6. Si rareté >= Rare ET LOOT_AI_GENERATION_MIN_RARITY atteint :
   → Appeler Gemini pour le nom, la description et l'image
   Sinon :
   → Piocher dans la table d'objets prédéfinis
```

### 3.2 — Génération des stats

**Nombre de stats par rareté :**

| Rareté | Stats | Effet spécial |
|--------|-------|---------------|
| Commun | 1 | Non |
| Peu commun | 2 | Non |
| Rare | 3 | 30% de chance |
| Épique | 4 | 60% de chance |
| Légendaire | 5 | 100% (toujours) |
| WTF | 5 | 100% (toujours, et il est absurde) |

**Calcul de la valeur d'une stat :**

```
Stat_base = Niveau_objet × Multiplicateur_rareté

Multiplicateurs de rareté :
  Commun      : × 1
  Peu commun  : × 2
  Rare        : × 3
  Épique      : × 4
  Légendaire  : × 5
  WTF         : × 6

Variance = random(100 - LOOT_STAT_VARIANCE, 100 + LOOT_STAT_VARIANCE)
Stat_finale = Stat_base × Variance / 100

Exemple : Épée Épique niveau 30
  ATQ_base = 30 × 4 = 120
  Variance = random(85, 115) = 107
  ATQ_finale = 120 × 107 / 100 = 128
```

**Choix des stats :**

La première stat est toujours la **stat principale du slot** (ATQ pour Arme, DEF pour Armure, etc.). Les stats suivantes sont tirées aléatoirement parmi les 6 stats, sans répétition, avec une pondération qui favorise les stats utiles au slot.

### 3.3 — Objets prédéfinis (Commun et Peu commun)

Les objets Commun et Peu commun sont piochés dans une table en base de données. Pas d'appel à Gemini.

Exemples par zone :

**Prairie des Débutants :**
| Nom | Slot | Rareté | Stats |
|-----|------|--------|-------|
| Épée Rouillée | Arme | Commun | ATQ |
| Bouclier en Bois | Armure | Commun | DEF |
| Bâton de Berger | Arme | Peu commun | ATQ, INT |
| Bottes Boueuses | Bottes | Commun | VIT |

**Mines du Nain Ivre :**
| Nom | Slot | Rareté | Stats |
|-----|------|--------|-------|
| Pioche Émoussée | Arme | Commun | ATQ |
| Casque de Mineur Cabossé | Casque | Commun | DEF |
| Chope de Courage | Accessoire | Peu commun | PV, CHA |
| Tablier en Cuir Renforcé | Armure | Peu commun | DEF, PV |

> Chaque zone a 15-20 objets prédéfinis. Total : ~150 objets de base.

### 3.4 — Objets générés par IA (Rare+)

**Requête Gemini (texte) :**

```json
{
  "slot": "arme",
  "rarity": "épique",
  "level": 30,
  "zone": "Le Marais de la Bureaucratie",
  "stats": { "ATQ": 128, "INT": 95, "VIT": 62, "CHA": 48 },
  "style": "Donne un nom et une description humoristique fantasy pour cet objet. Le nom doit être absurde et drôle. La description en 1-2 phrases. Réponse en JSON : {name, description}"
}
```

**Réponse attendue :**
```json
{
  "name": "Tampon Officiel de Destruction +4",
  "description": "Un tampon administratif si puissant qu'il valide la mort de vos ennemis. Encre rouge incluse."
}
```

**Requête Gemini (image — Imagen) :**
```
"A cartoon RPG item icon: [nom de l'objet]. Fantasy humorous style, colorful,
white background, game item art, high quality icon"
```

**Stockage :**
- L'objet est sauvegardé en base avec un flag `ai_generated = 1`
- L'image est stockée dans `storage/loot/{item_id}.webp`
- Jamais regénéré une fois créé

**Fallback si Gemini indisponible :**
- Nom généré par un système de templates : `{Adjectif} {Objet} {Suffixe}`
  - Adjectifs : "Redoutable", "Fumant", "Bureaucratique", "Douteux"...
  - Suffixes : "du Destin", "de la Mort Lente", "+3 (environ)", "des Impôts"...
- Image : placeholder générique par slot et rareté

---

## 4. Effets spéciaux des objets

### 4.1 — Pool d'effets par rareté

Les effets spéciaux sont tirés d'un pool. Plus la rareté est haute, plus les effets sont puissants.

**Effets Rare (pool de base) :**

| ID | Nom | Effet | S'applique à |
|----|-----|-------|-------------|
| R01 | Vampirisme | Soigne le porteur de 3% des dégâts infligés | Armes |
| R02 | Épines | Renvoie 5% des dégâts reçus à l'attaquant | Armures, Casques |
| R03 | Célérité | +5% VIT | Bottes |
| R04 | Chercheur d'Or | +10% or trouvé | Accessoires |
| R05 | Résistance au Feu | Réduit les dégâts de feu de 20% | Armures, Casques |
| R06 | Coup Double | 5% de chance de frapper deux fois | Armes |
| R07 | Régénération | Récupère 1% PV max par tour | Armures, Accessoires |
| R08 | Chance du Débutant | +3% critique | Armes, Accessoires |
| R09 | Solidité | Durabilité +50% | Tous |
| R10 | Chercheur de Loot | +5% de chance de loot | Accessoires |

**Effets Épique (pool amélioré) :**

| ID | Nom | Effet | S'applique à |
|----|-----|-------|-------------|
| E01 | Vampirisme Majeur | Soigne de 6% des dégâts infligés | Armes |
| E02 | Bouclier Magique | 10% de chance d'annuler un sort ennemi | Armures, Casques |
| E03 | Vitesse de l'Éclair | +10% VIT + 5% esquive | Bottes |
| E04 | Fortune | +20% or trouvé | Accessoires |
| E05 | Exécuteur | +15% dégâts contre les ennemis sous 25% PV | Armes |
| E06 | Aura Glaciale | 10% de chance de Ralentir l'attaquant 1 tour | Armures |
| E07 | Régénération Majeure | 2% PV max par tour | Armures, Accessoires |
| E08 | Précision Mortelle | +8% critique, les critiques ignorent 10% DEF | Armes |
| E09 | Indestructible | Durabilité infinie | Tous |
| E10 | Magnétisme à Loot | +10% chance de loot + rareté améliorée 5% | Accessoires |

**Effets Légendaire (pool unique) :**

| ID | Nom | Effet | S'applique à |
|----|-----|-------|-------------|
| L01 | Fléau des Dieux | 10% de chance d'infliger 200% des dégâts | Armes |
| L02 | Immortalité Partielle | 1 fois par combat, si PV = 0, revient à 20% PV | Armures |
| L03 | Hâte Absolue | +20% VIT, agit toujours en premier | Bottes |
| L04 | Midas | Chaque ennemi tué rapporte le double d'or | Accessoires |
| L05 | Absorption Vitale | 10% des dégâts infligés soignent TOUTE l'équipe à parts égales | Armes |
| L06 | Reflet | 20% de chance de renvoyer un sort ennemi à son lanceur | Armures, Casques |
| L07 | Régénération Totale | 3% PV max par tour + retire 1 effet négatif par tour | Accessoires |
| L08 | Frappe Dimensionnelle | Les attaques ignorent 30% de la DEF | Armes |
| L09 | Résonance de Groupe | Toute l'équipe gagne +5% de la stat principale de l'objet | Tous |
| L10 | Chance Insolente | +15% critique + +15% esquive | Accessoires |

**Effets WTF (uniques, absurdes, puissants mais imprévisibles) :**

| ID | Nom | Effet | Conditions |
|----|-----|-------|-----------|
| W01 | Inversion | Les dégâts subis soignent et les soins blessent. Oui, vraiment. | Actif uniquement les tours pairs |
| W02 | Polyglotte Involontaire | Le héros parle une langue aléatoire. 20% de chance par tour que les ordres soient mal compris (action aléatoire) MAIS les buffs ont +30% d'efficacité | Permanent |
| W03 | Loi de Murphy | Tout ce qui peut mal tourner tourne mal. +30% de déclenchement du trait négatif MAIS les effets de la Branche du Défaut sont doublés | Permanent |
| W04 | Aura de Malchance | Les ennemis proches ont -15% de toutes stats. Les alliés proches aussi (-8%) | Permanent |
| W05 | Téléportation Aléatoire | À chaque tour, 15% de chance de téléporter le héros. Soit il esquive toutes les attaques ce tour, soit il apparaît au milieu des ennemis et prend 20% PV max de dégâts | Par tour |
| W06 | Objection ! | Le héros peut annuler la dernière action d'un ennemi une fois tous les 10 tours. L'ennemi doit recommencer son tour avec une action aléatoire | CD : 10 tours |
| W07 | Clone Maladroit | Crée un clone du héros avec 30% de ses stats. Le clone ne peut pas être contrôlé et attaque des cibles aléatoires. Si le clone meurt, le héros est étourdi 1 tour | Au début du combat |
| W08 | Monologue du Méchant | Quand un boss passe sous 50% PV, il fait un monologue de 2 tours pendant lesquels il n'attaque pas mais récupère 5% PV/tour. S'applique aussi aux alliés boss de quête | Contre les boss |
| W09 | Gravité Optionnelle | Le héros ignore la gravité. +30% esquive (il flotte) mais -20% ATQ physique (pas d'appui au sol). Les attaques magiques gagnent +20% à la place | Permanent |
| W10 | Quatrième Mur | Le héros "sait" qu'il est dans un jeu. +10% à toutes les stats. Mais il fait régulièrement des commentaires qui cassent l'immersion et déconcentrent l'équipe (-5% toutes stats alliés 1 tour, 15% de chance/tour) | Permanent |

### 4.2 — Cumul des effets

**Règles de cumul :**

| Situation | Règle |
|-----------|-------|
| Même effet sur 2 objets (ex: deux Vampirisme) | Les % s'additionnent |
| Effet passif + buff temporaire | S'empilent multiplicativement : stat × (100 + passif) / 100 × (100 + buff) / 100 |
| Deux effets contraires (ex: +VIT et -VIT) | S'additionnent algébriquement |
| Effet WTF + effet WTF | Les deux s'appliquent (chaos total, c'est le but) |
| Effet par combat (ex: Immortalité Partielle) | Se déclenche une seule fois même avec 2 objets similaires |
| Plafonds | Esquive max 40%, Critique max 50%, Vampirisme max 15% |

### 4.3 — Durabilité

```
Chaque objet équipé perd LOOT_DURABILITY_LOSS_PER_COMBAT (1) point de durabilité par combat.

Quand durabilité = 0 :
  - L'objet est "cassé" → ne donne plus aucun bonus
  - L'objet reste équipé mais grisé dans l'interface
  - Le Narrateur commente : "Ton épée vient de se casser. En plein combat. Bravo."

Réparation :
  Coût = (Durabilité_max - Durabilité_actuelle) × Niveau_objet × LOOT_REPAIR_COST_MULTIPLIER
  
  Exemple : Épée Épique niv 30, durabilité 40/100
    Coût = (100 - 40) × 30 × 2 = 3600 or

Exception : Objets avec effet "Indestructible" (E09) → jamais de perte de durabilité
Exception : Objets WTF → durabilité de base 30 (cassent très vite)
```

---

## 5. Système de Crafting — La Forge de Gérard

### 5.1 — Les 3 opérations

| Opération | Description | Coût |
|-----------|-------------|------|
| **Fusion** | Combiner 3 objets → 1 objet de rareté supérieure | Or + les 3 objets |
| **Démontage** | Détruire 1 objet → récupérer des matériaux | Gratuit |
| **Recette** | Combiner des matériaux spécifiques → objet prédéfini | Matériaux + or |

### 5.2 — Fusion

```
Règles de fusion :
  1. Nécessite CRAFT_FUSION_COUNT (3) objets de MÊME rareté
  2. Les objets n'ont PAS besoin d'être du même slot
  3. Coût en or = Niveau_moyen_objets × 50 × Multiplicateur_rareté

Multiplicateurs de coût :
  Commun → Peu commun     : × 1
  Peu commun → Rare       : × 2
  Rare → Épique            : × 4
  Épique → Légendaire      : × 8
  Légendaire → WTF         : × 20

Résolution :
  1. Jet de succès : random(1, 100) <= CRAFT_FUSION_SUCCESS (85%) ?
  2. Si succès :
     a. Jet de critique : random(1, 100) <= CRAFT_FUSION_CRIT (10%) ?
        - Critique : l'objet résultant est de 2 raretés au-dessus au lieu d'1
          (ex: 3 Communs → Rare directement)
        - Plafonné à WTF
     b. Le slot du résultat est choisi aléatoirement parmi les 3 slots des ingrédients
        (si 3 slots différents) ou le même slot (si identiques)
     c. Le niveau du résultat = moyenne des 3 niveaux + random(0, 3)
     d. Si rareté >= Rare → Gemini génère le nom/description/image
        en tenant compte des 3 objets d'entrée
     e. Les stats sont générées selon les règles normales de la rareté résultante
  3. Si échec :
     a. Les 3 objets sont détruits
     b. Le joueur récupère 1 matériau de la rareté des objets détruits
     c. Gérard s'excuse
     d. Le Narrateur se moque

Cas spécial — Fusion de 3 objets du même slot :
  → Le résultat est GARANTI d'être du même slot
  → +10% de chance de succès (les matériaux sont compatibles)

Cas spécial — Fusion de 3 objets avec effet spécial :
  → 40% de chance que le résultat hérite d'un des effets spéciaux
  → En plus de la chance normale d'avoir un effet de sa rareté
```

**Requête Gemini pour fusion :**

```json
{
  "input_items": [
    { "name": "Épée Rouillée +2", "slot": "arme" },
    { "name": "Baguette de Pain Magique", "slot": "arme" },
    { "name": "Chaussette du Destin", "slot": "accessoire" }
  ],
  "result_rarity": "rare",
  "result_slot": "arme",
  "result_stats": { "ATQ": 87, "INT": 45, "CHA": 32 },
  "style": "Génère un nom et description humoristiques pour l'objet résultant de la fusion de ces 3 objets. Le résultat doit être absurde et faire référence aux ingrédients. JSON : {name, description}"
}
```

### 5.3 — Démontage

```
Entrée : 1 objet à détruire
Sortie : Matériaux

L'objet est détruit définitivement.
Nombre de matériaux = random(CRAFT_DISMANTLE_MATERIAL_MIN, CRAFT_DISMANTLE_MATERIAL_MAX)

Type de matériaux selon la rareté de l'objet :

  Commun       → Ferraille (1-3 unités)
  Peu commun   → Ferraille (2-4) + chance 30% Essence Mineure (1)
  Rare         → Essence Mineure (2-4) + Cristal Brut (1)
  Épique       → Cristal Brut (2-3) + Essence Majeure (1)
  Légendaire   → Essence Majeure (2-3) + Fragment Stellaire (1)
  WTF          → Fragment Stellaire (1-2) + Bout de Ficelle Cosmique (1)

Bonus par slot :
  Arme    → +1 Ferraille supplémentaire
  Armure  → +1 Ferraille supplémentaire
  Casque  → rien de bonus
  Bottes  → +1 Cuir (matériau bonus)
  Accessoire → +1 Gemme Brute (matériau bonus)
  Truc Bizarre → matériau aléatoire bonus
```

### 5.4 — Matériaux

| Matériau | Provenance | Utilisation |
|----------|------------|-------------|
| Ferraille | Démontage Commun/Peu commun, drop | Recettes de base, réparation discount |
| Cuir | Démontage bottes, drop monstres | Recettes d'armures légères |
| Gemme Brute | Démontage accessoires, drop | Recettes d'accessoires |
| Essence Mineure | Démontage Peu commun/Rare | Recettes intermédiaires |
| Cristal Brut | Démontage Rare/Épique | Recettes avancées |
| Essence Majeure | Démontage Épique/Légendaire | Recettes de haut niveau |
| Fragment Stellaire | Démontage Légendaire/WTF, boss | Recettes légendaires |
| Bout de Ficelle Cosmique | Démontage WTF uniquement | Recettes WTF |
| Larme de Gérard | Échec de fusion (rare, 10%) | Recettes secrètes |
| Poussière de Narrateur | Événements spéciaux | Recettes cosmétiques |

### 5.5 — Recettes

Les recettes sont **découvertes** progressivement. Deux méthodes :

```
1. Découverte par crafting :
   Chaque fusion a CRAFT_RECIPE_DISCOVER_CHANCE (5%) de révéler une recette
   La recette est liée à la zone actuelle du joueur

2. Découverte par quêtes :
   Certaines quêtes (zone et événements) donnent des recettes en récompense
```

**Recettes de base (disponibles dès le début) :**

| Recette | Matériaux | Coût or | Résultat |
|---------|-----------|---------|----------|
| Potion de Soin | 3 Ferraille + 2 Essence Mineure | 50 | Soigne 30% PV max |
| Potion de Soin+ | 2 Essence Mineure + 1 Cristal Brut | 150 | Soigne 60% PV max |
| Parchemin de Fuite | 5 Ferraille | 30 | Fuite automatique garantie (1 utilisation) |
| Pierre d'Aiguisage | 4 Ferraille + 1 Gemme Brute | 80 | +10% ATQ pour 5 combats |
| Kit de Réparation | 10 Ferraille | 100 | Répare 30 points de durabilité |

**Recettes découvrables (exemples par zone) :**

| Zone | Recette | Matériaux | Résultat |
|------|---------|-----------|----------|
| Prairie | Amulette du Débutant | 5 Ferraille + 3 Cuir | Accessoire Peu commun : +5% XP |
| Forêt | Arc des Elfes Vexés | 3 Essence Mineure + 5 Cuir | Arme Rare : ATQ + dégâts bonus aux elfes |
| Mines | Casque du Nain Blindé | 8 Ferraille + 2 Cristal Brut | Casque Rare : DEF + résistance étourdissement |
| Marais | Formulaire en Triple | 3 Cristal Brut + 1 Essence Majeure | Accessoire Épique : Paperasserie sur ennemi 1×/combat |
| Tour | Orbe Instable | 2 Essence Majeure + 1 Fragment Stellaire | Arme Épique : INT + variance de dégâts augmentée |
| Cimetière | Pelle du Fossoyeur | 5 Cristal Brut + 2 Essence Majeure | Arme Épique : ATQ + bonus contre morts-vivants |

**Recettes secrètes (matériaux rares) :**

| Recette | Matériaux | Résultat |
|---------|-----------|----------|
| Tablier de Gérard | 5 Larme de Gérard + 10 Ferraille | Armure Légendaire : DEF + immunité aux échecs de craft |
| Micro du Narrateur | 3 Poussière de Narrateur + 1 Fragment Stellaire | Accessoire Légendaire : +20% XP + commentaires du Narrateur améliorés |
| Ficelle Universelle | 3 Bout de Ficelle Cosmique | Accessoire WTF : Relie aléatoirement 2 ennemis — dégâts partagés entre eux |

---

## 6. Commentaires de Gérard (le forgeron)

Gérard a CRAFT_GERARD_HUMOR_CHANCE (30%) de faire un commentaire à chaque opération. Exemples :

**Fusion réussie :**
> "Eh ben, ça a marché ! J'suis aussi surpris que vous."
> "Un chef-d'œuvre ! Enfin, c'est un mot fort. Disons que ça coupe."

**Fusion critique :**
> "ATTENDEZ — ça brille ?! J'ai jamais fait ça de ma vie !"
> "Alors là... je sais pas ce que j'ai fait, mais c'est magnifique."

**Fusion ratée :**
> "Oups. C'est... fondu. Désolé. Enfin bon, c'était déjà pas terrible."
> "J'ai cassé vos trois trucs. Par contre j'ai trouvé ce bout de ferraille, ça vous dit ?"

**Démontage :**
> "Vous êtes sûr ? Il était pas si mal ce... ah, c'est fait. Bon."
> "Démontage express ! Par contre j'ai mis le feu à l'établi. C'est normal."

**Recette découverte :**
> "Oh, j'ai trouvé un truc ! Enfin, je crois. C'est soit une recette, soit une liste de courses."

> En phase 2+, Gemini génère des répliques uniques basées sur les objets impliqués.

---

## 7. Vente d'objets

```
Prix de vente = Prix_base × LOOT_SELL_PERCENT / 100

Prix_base par rareté et niveau :
  Commun      : Niveau × 5
  Peu commun  : Niveau × 15
  Rare        : Niveau × 40
  Épique      : Niveau × 100
  Légendaire  : Niveau × 300
  WTF         : Niveau × 500

Exemple : Épée Rare niveau 25
  Prix_base = 25 × 40 = 1000 or
  Prix_vente = 1000 × 30 / 100 = 300 or

Objets cassés (durabilité 0) :
  Prix_vente = Prix_vente / 2
```

---

## 8. Inventaire

### 8.1 — Limites

| Paramètre | Valeur |
|-----------|--------|
| `INVENTORY_MAX_ITEMS` | 100 |
| `INVENTORY_EXPAND_COST` | 500 (par tranche de 20 slots) |
| `INVENTORY_MAX_EXPANDED` | 200 |
| `INVENTORY_MATERIALS_NO_LIMIT` | Vrai (les matériaux n'ont pas de limite) |

### 8.2 — Gestion

- Les objets équipés ne comptent PAS dans l'inventaire
- Les matériaux de craft sont stockés séparément (pas de limite)
- Les potions/consommables ont un stack max de 99
- Quand l'inventaire est plein, le loot est auto-vendu au prix de vente
  - Le Narrateur prévient : "Inventaire plein. J'ai vendu ta Dague Moisie. De rien."

---

## 9. Objets de boss mondiaux

Les objets donnés au top 10% des contributeurs d'un boss mondial sont **uniques et thématiques**.

### 9.1 — Génération

```
Quand un boss mondial est vaincu :

1. Le serveur envoie à Gemini :
   {
     "boss_name": "Le Kraken Comptable",
     "boss_description": "Un kraken géant qui audite les aventuriers",
     "boss_mechanics": ["stat_swap", "audit"],
     "rarity": "légendaire",
     "count": 6 (nombre de variantes à générer — 1 par slot)
   }

2. Gemini génère 6 objets thématiques (1 par slot) :
   - Arme : "Tentacule-Stylo du Kraken" (ATQ + INT)
   - Armure : "Costume-Cravate en Écailles" (DEF + résistance audit)
   - Casque : "Monocle du Comptable Marin" (INT + CHA)
   - Bottes : "Ventouses de Bureau" (VIT + esquive)
   - Accessoire : "Calculatrice Tentaculaire" (CHA + bonus or)
   - Truc Bizarre : "Encrier d'Encre de Kraken" (effet WTF unique)

3. Chaque joueur du top 10% reçoit un objet aléatoire parmi les 6
   - Pas de doublon pour le même joueur
   - Si le joueur avait déjà un objet de ce boss → il reçoit un autre slot

4. Les objets sont marqués en base : boss_origin = boss_id
   - Affichage spécial dans le profil (collection de trophées)
```

### 9.2 — Puissance des objets de boss

```
Les objets de boss sont de rareté Légendaire avec :
  - Stats = Niveau_moyen_serveur × Multiplicateur_légendaire (5)
  - 1 effet Légendaire garanti
  - 1 effet thématique unique lié au boss (non trouvable autrement)

Effets thématiques de boss (exemples) :
  Kraken Comptable → "Audit Offensif" : 15% de chance d'échanger 2 stats d'un ennemi pendant 2 tours
  Dragon Retraité → "Pause Café" : 1×/combat, skip un tour pour récupérer 30% PV max
  Hydre Syndicaliste → "Grève Solidaire" : Quand un allié est touché, 20% de chance que le porteur absorbe 30% des dégâts
```

---

## 10. Résumé des formules clés

| Formule | Expression |
|---------|------------|
| **Drop chance** | `random(1,100) <= LOOT_DROP_CHANCE` |
| **Stat d'objet** | `Niveau × Mult_rareté × random(85,115) / 100` |
| **Coût fusion** | `Niveau_moyen × 50 × Mult_coût_rareté` |
| **Coût réparation** | `(Dura_max - Dura_actuelle) × Niveau × REPAIR_MULT` |
| **Prix vente** | `Niveau × Base_rareté × LOOT_SELL_PERCENT / 100` |
| **Durabilité restante** | `Dura_base - (combats × DURA_LOSS)` |
| **Matériaux démontage** | `random(MIN, MAX) + bonus_slot` |
