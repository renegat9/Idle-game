# 📜 Système de Quêtes & Aventures — Effets Détaillés

## 1. Règle fondamentale

> **Aucun effet de quête n'est permanent et négatif.** Les conséquences négatives sont toujours temporaires et récupérables. Le joueur ne peut jamais perdre définitivement un héros, un objet ou une progression à cause d'un choix de quête. Les effets positifs, en revanche, peuvent être permanents.

---

## 2. Constantes paramétrables (table `game_settings`)

| Clé | Valeur par défaut | Description |
|-----|-------------------|-------------|
| `QUEST_DAILY_COUNT` | 3 | Quêtes quotidiennes par jour |
| `QUEST_DAILY_STEPS_MIN` | 3 | Étapes minimum d'une quête quotidienne |
| `QUEST_DAILY_STEPS_MAX` | 5 | Étapes maximum d'une quête quotidienne |
| `QUEST_ZONE_STEPS_MIN` | 5 | Étapes minimum d'une quête de zone |
| `QUEST_ZONE_STEPS_MAX` | 7 | Étapes maximum d'une quête de zone |
| `QUEST_WTF_STEPS_MIN` | 7 | Étapes minimum d'une quête WTF |
| `QUEST_WTF_STEPS_MAX` | 12 | Étapes maximum d'une quête WTF |
| `QUEST_EVENT_STEPS` | 5 | Étapes d'une quête événementielle |
| `QUEST_BUFF_DURATION_SHORT` | 10 | Combats (durée buff court) |
| `QUEST_BUFF_DURATION_MEDIUM` | 30 | Combats (durée buff moyen) |
| `QUEST_BUFF_DURATION_LONG` | 100 | Combats (durée buff long) |
| `QUEST_DEBUFF_DURATION_MAX` | 20 | Combats (durée max d'un debuff) |
| `QUEST_HERO_ABSENCE_MAX` | 60 | Minutes (absence max d'un héros temporairement perdu) |
| `QUEST_SURPRISE_CHANCE` | 15 | % de chance d'événement surprise par étape |
| `QUEST_REPUTATION_PER_QUEST` | 10 | Points de réputation gagnés par quête réussie |
| `QUEST_REPUTATION_ZONE_UNLOCK` | 100 | Réputation nécessaire pour débloquer le contenu bonus d'une zone |

---

## 3. Types de quêtes

### 3.1 — Vue d'ensemble

| Type | Disponibilité | Étapes | Difficulté | Source des effets |
|------|---------------|--------|------------|-------------------|
| Quête de zone | Permanente, séquentielle | 5-7 | Croissante | Pré-écrites en base |
| Quête quotidienne | 3/jour, rotation | 3-5 | Adaptée au niveau | Templates + Gemini |
| Quête événementielle | Pendant un boss mondial | 5 | Haute | Pré-écrites + IA |
| Quête WTF | Rare (5% par jour d'en voir une) | 7-12 | Variable | Gemini principalement |
| Aventure idle | Permanente, automatique | 1 (micro-événement) | Zone actuelle | Pool d'événements |

### 3.2 — Quêtes de zone

- **10 quêtes par zone**, jouées dans l'ordre
- Débloquent la progression narrative de la zone
- La dernière quête de chaque zone débloque la zone suivante
- Récompenses fixes (pré-définies en base)
- Rejouables pour l'XP mais pas les récompenses uniques

### 3.3 — Quêtes quotidiennes

- **3 par jour**, renouvelées à minuit (heure serveur)
- Générées à partir de templates enrichis par Gemini
- Récompenses : XP, or, matériaux, chance de loot Rare
- Si non complétées, elles disparaissent (pas de report)

### 3.4 — Quêtes événementielles

- Apparaissent quand un boss mondial est actif
- Contribuent aux dégâts du boss (bonus de contribution)
- Récompenses liées au thème du boss
- Disparaissent quand le boss est vaincu

### 3.5 — Quêtes WTF

- 5% de chance par jour qu'une quête WTF apparaisse
- Longues, absurdes, imprévisibles
- Récompenses exceptionnelles (loot Épique+ garanti)
- Pas de limite de temps (reste disponible jusqu'à complétion)
- Gemini génère le scénario, les embranchements et les récompenses

### 3.6 — Aventures idle (micro-événements)

- Se déclenchent pendant l'exploration idle automatique
- 1 événement toutes les ~30 minutes d'exploration
- Le joueur voit le résultat à la reconnexion
- Effets mineurs mais fréquents (or, petit buff, narration)

---

## 4. Catégories d'effets

### 4.1 — Buffs temporaires sur les héros

Des bonus appliqués à un ou plusieurs héros pour un nombre de combats limité.

| ID | Nom | Effet | Durée | Source typique |
|----|-----|-------|-------|----------------|
| B01 | Bénédiction du Village | +10% toutes stats | MEDIUM (30 combats) | Quête de zone (aider un village) |
| B02 | Rage de la Victoire | +15% ATQ | SHORT (10 combats) | Choix agressif réussi |
| B03 | Sagesse Ancienne | +15% INT + 10% XP | MEDIUM (30 combats) | Quête avec un PNJ sage |
| B04 | Pieds Légers | +20% VIT | SHORT (10 combats) | Choix de fuite réussi |
| B05 | Peau de Dragon | +20% DEF | SHORT (10 combats) | Quête de zone (victoire contre boss) |
| B06 | Baraka | +10% CHA + 5% loot | MEDIUM (30 combats) | Choix chanceux |
| B07 | Inspiration Épique | +10% toutes stats | LONG (100 combats) | Quête WTF réussie |
| B08 | Festin | +15% PV max | MEDIUM (30 combats) | Banquet / taverne dans une quête |
| B09 | Protection Divine | -15% dégâts reçus | SHORT (10 combats) | Quête avec le Prêtre impliqué |
| B10 | Soif de Sang | +20% ATQ mais -10% DEF | MEDIUM (30 combats) | Choix violent réussi |
| B11 | Concentration Totale | +10% critique + 10% esquive | SHORT (10 combats) | Quête d'entraînement |
| B12 | Aura du Héros | Toute l'équipe +5% toutes stats | MEDIUM (30 combats) | Quête de zone finale (acte héroïque) |
| B13 | Chance du Survivant | +15% esquive | SHORT (10 combats) | Survie à un piège |
| B14 | Brise Magique | +15% résistance magique | MEDIUM (30 combats) | Quête en zone magique |
| B15 | Trésor Caché | +20% or trouvé | LONG (100 combats) | Découverte d'une carte au trésor |

### 4.2 — Debuffs temporaires sur les héros

Des malus toujours limités dans le temps. Jamais plus de `QUEST_DEBUFF_DURATION_MAX` combats.

| ID | Nom | Effet | Durée max | Source typique |
|----|-----|-------|-----------|----------------|
| D01 | Malédiction Mineure | -10% toutes stats | 20 combats | Mauvais choix dans un temple |
| D02 | Gueule de Bois | -15% VIT + -10% INT | 10 combats | Taverne / banquet raté |
| D03 | Peur Résiduelle | -10% ATQ + 5% chance de fuir | 15 combats | Rencontre terrifiante |
| D04 | Empoisonnement Léger | -5% PV max par combat (soignable au repos) | 10 combats | Piège empoisonné |
| D05 | Honte Publique | -15% CHA | 20 combats | Échec humiliant devant PNJ |
| D06 | Rhume du Donjon | -10% toutes stats | 10 combats | Explorer sans torche |
| D07 | Distraction Mentale | -10% INT + -5% VIT | 15 combats | Quête philosophique ratée |
| D08 | Encombrement | -15% VIT | 10 combats | Transporter un objet lourd de quête |
| D09 | Mauvais Karma | -10% CHA + -5% loot | 15 combats | Choix égoïste |
| D10 | Fatigue de Quête | -5% toutes stats | 10 combats | Quête WTF très longue |

> **Règle :** Un debuff disparaît TOUJOURS au bout de sa durée max. Il n'y a aucun moyen qu'un debuff devienne permanent. Le joueur peut aussi visiter la Taverne pour payer en or et retirer un debuff immédiatement (coût : 100 × niveau du héros).

### 4.3 — Effets sur l'équipe

| ID | Nom | Effet | Durée/Récupération | Source typique |
|----|-----|-------|-------------------|----------------|
| EQ01 | Héros Perdu | Un héros est temporairement indisponible (séparé du groupe) | Max QUEST_HERO_ABSENCE_MAX (60 min), revient automatiquement | Piège de séparation, choix de se séparer |
| EQ02 | Héros Blessé | Un héros est à 1 PV et ne peut pas être soigné par les potions pendant X combats | 5 combats (se soigne en idle entre combats normalement) | Combat de boss de quête perdu |
| EQ03 | Renfort Temporaire | Un PNJ rejoint l'équipe comme 6ème membre pendant la quête | Durée de la quête uniquement | Quête de zone (allié PNJ) |
| EQ04 | Recrue Surprise | Un héros recruitable gratuitement apparaît à la Taverne (race/classe/trait révélés) | Le joueur a 24h pour le recruter | Quête WTF, quête de zone rare |
| EQ05 | Échange de Position | Deux héros échangent leur position dans l'ordre d'initiative pendant X combats | 15 combats | Événement comique de quête |
| EQ06 | Lien Fraternel | Deux héros gagnent +10% toutes stats quand ils sont dans la même équipe | 50 combats | Quête narrative (vécu ensemble) |
| EQ07 | Rivalité | Deux héros gagnent +15% ATQ mais -10% DEF quand ensemble | 30 combats | Dispute dans une quête |
| EQ08 | Mentor | Un héros haut niveau donne +20% XP à un héros bas niveau | 50 combats | Quête de zone (entraînement) |

> **Héros Perdu (EQ01) :** Le héros revient TOUJOURS après 60 minutes max. Le joueur reçoit une notification. Le Narrateur commente : "Il était coincé dans un placard. Ne posez pas de questions."

### 4.4 — Effets sur le monde

| ID | Nom | Effet | Permanence | Source typique |
|----|-----|-------|-----------|----------------|
| M01 | Chemin Débloqué | Ouvre un raccourci entre deux zones (réduction temps de voyage) | Permanent | Quête de zone (explorer un passage) |
| M02 | Zone Secrète | Débloque une sous-zone cachée avec ses propres mobs et loot | Permanent | Quête WTF, quête de zone finale |
| M03 | Point de Repos | Débloque un point de repos dans la zone (soin entre combats +20%) | Permanent | Aider un PNJ aubergiste |
| M04 | Marchand Ambulant | Un marchand spécial apparaît dans la zone pendant 24h (vend des objets rares) | 24 heures | Événement surprise, quête quotidienne |
| M05 | Malédiction de Zone | La zone a +10% de mobs élites pendant 2h (meilleur loot aussi) | 2 heures | Mauvais choix dans un donjon sacré |
| M06 | Bénédiction de Zone | +15% XP dans la zone pendant 4h | 4 heures | Bon choix dans un temple |
| M07 | Modification du Terrain | Un nouveau type d'ennemi apparaît dans la zone (permanent, enrichit le contenu) | Permanent | Quête de zone narrative |
| M08 | Camp de Base | Installe un camp avancé → réduit le temps de voyage vers cette zone de 50% | Permanent | Quête d'exploration avancée |
| M09 | Portail Instable | Téléporteur vers une zone aléatoire, change chaque jour | Permanent (le portail reste, la destination change) | Quête WTF |
| M10 | Donjon Caché | Débloque un donjon spécial bonus dans la zone (loot amélioré, difficulté +) | Permanent | Dernière quête d'une chaîne de zone |

### 4.5 — Effets narratifs (Réputation & PNJ)

#### Système de réputation

Chaque zone a un **score de réputation** (0 à 200) :

```
Gagner de la réputation :
  - Quête de zone réussie : +QUEST_REPUTATION_PER_QUEST (10)
  - Quête quotidienne dans cette zone : +5
  - Choix "bon" dans les embranchements : +3 à +8
  - Aider un PNJ : +5

Perdre de la réputation (temporairement) :
  - Choix égoïste ou violent : -3 à -5
  - Échouer une quête de zone : -2
  - Note : la réputation ne descend JAMAIS sous 0

Paliers de réputation :
  0-24    : Inconnu — pas d'effet
  25-49   : Connu — les PNJ donnent des indices sur les quêtes
  50-74   : Respecté — -10% prix chez les marchands de la zone
  75-99   : Célèbre — accès aux quêtes secondaires de zone
  100+    : Héros Local — récompenses de zone améliorées (+20% loot)
            + commentaires uniques du Narrateur
  150+    : Légende — débloquer le contenu bonus de la zone
            (sous-zones, PNJ spéciaux, recettes exclusives)
  200     : Maximum — titre spécial pour cette zone
```

#### Relations PNJ

Certaines quêtes impliquent des **PNJ récurrents** avec un score de relation :

| PNJ | Zone | Score initial | Effets de relation |
|-----|------|---------------|-------------------|
| Gérard le Forgeron | Toutes | 50 | Haute relation → réduction coûts de craft, recettes exclusives |
| Narrator (Le Narrateur) | Toutes | 0 | Ne change jamais. Il vous déteste quoi qu'il arrive. |
| Elara l'Elfe Vexée | Forêt | 0 | Haute relation → accès à l'armurerie elfique |
| Thorin le Nain Ivre | Mines | 25 | Haute relation → bonus de minage, bière magique |
| Le Bureaucrate en Chef | Marais | 0 | Haute relation → passe-droit administratif (skip certains obstacles) |
| Magus le Distrait | Tour | 10 | Haute relation → sorts bonus, enchantements gratuits |
| Le Syndicaliste Squelette | Cimetière | 0 | Haute relation → les morts-vivants n'attaquent plus en premier |
| Le Dragon Retraité (amical) | Volcan | 0 | Haute relation → monture dragon temporaire (voyage instantané) |

```
Score de relation : 0 à 100

Gagner :
  - Aider le PNJ dans une quête : +5 à +15
  - Choix qui favorise le PNJ : +3 à +8
  - Offrir un cadeau (objet ou or) : +5 par cadeau (max 1 par jour)

Le score ne descend JAMAIS (pas de perte permanente)
Mais certains choix empêchent de gagner des points pendant X quêtes

Paliers :
  0-24  : Méfiant — dialogue de base
  25-49 : Neutre — donne des indices, vend normalement
  50-74 : Amical — prix réduits, quêtes secondaires
  75-99 : Allié — récompenses exclusives, aide en combat (rare)
  100   : Meilleur Ami — effet unique permanent par PNJ
```

**Effets "Meilleur Ami" (relation 100) :**

| PNJ | Effet permanent |
|-----|-----------------|
| Gérard | Fusion : +10% succès, +5% critique |
| Elara | Équipement elfique achetable (objets Épique exclusifs) |
| Thorin | Potion "Bière de Thorin" craftable (buff +20% ATQ +20% PV, -10% INT, 30 combats) |
| Le Bureaucrate | Quêtes du Marais ont 1 étape de moins |
| Magus | Enchantement gratuit 1×/semaine (ajoute un effet Rare à un objet) |
| Le Syndicaliste | Les morts-vivants de la zone ont -10% stats (ils font grève) |
| Le Dragon | Voyage instantané 1×/jour vers n'importe quelle zone débloquée |

### 4.6 — Événements surprise

Se déclenchent aléatoirement pendant les quêtes et aventures idle. Le joueur ne peut pas les prédire.

#### Événements surprise en quête active

Chance par étape : `QUEST_SURPRISE_CHANCE` (15%)

| ID | Nom | Catégorie | Effet | Condition |
|----|-----|-----------|-------|-----------|
| S01 | Marchand Errant | Positif | Un marchand propose 3 objets à prix réduit (-50%) | Aucune |
| S02 | Embuscade | Négatif temp. | Combat surprise contre des ennemis +20% stats | Aucune |
| S03 | Coffre Piégé | Mixte | 50% : loot Rare+ / 50% : debuff D01 (Malédiction Mineure) | Aucune |
| S04 | PNJ en Détresse | Positif | Sauver → réputation +10 dans la zone + buff B06 (Baraka) | Aucune |
| S05 | Fontaine Mystérieuse | Mixte | Boire : 60% buff B01 (Bénédiction) / 40% debuff D02 (Gueule de Bois) | Zone non-magique |
| S06 | Carte au Trésor | Positif | Débute une micro-quête bonus (2 étapes) → loot garanti | Aucune |
| S07 | Fantôme Bavard | Narratif | Un fantôme raconte une histoire → indice sur une quête secrète ou recette | Aucune |
| S08 | Éboulement | Négatif temp. | L'équipe doit faire un détour → +1 étape à la quête mais +10% loot au boss | Donjons |
| S09 | Rival Aventurier | Mixte | Un PNJ rival vous défie. Gagner : buff B02 (Rage) + or. Perdre : debuff D05 (Honte) | Aucune |
| S10 | Trait en Action | Comique | Le trait négatif d'un héros se déclenche hors combat avec un effet narratif. Pas de malus mécanique, juste une scène drôle | Toujours un héros avec un trait |
| S11 | Météo Bizarre | Mixte | Pluie de poissons, neige en été, etc. 70% : +5% esquive (sol glissant pour tous) / 30% : -5% VIT | Extérieur |
| S12 | Le Narrateur Intervient | Narratif | Le Narrateur change un élément de la quête en direct ("En fait non, c'était pas un gobelin, c'était un troll. Bonne chance.") | Aucune |
| S13 | Inspiration Subite | Positif | Un héros aléatoire gagne un point de talent temporaire pendant 50 combats | Quêtes WTF uniquement |
| S14 | Raccourci Dangereux | Choix | Raccourci : skip 2 étapes mais combat difficile. Chemin normal : pas de risque | Quêtes 5+ étapes |
| S15 | Trou Dimensionnel | Rare (2%) | Téléporte dans une quête bonus dans un lieu absurde. Récompenses doublées | Quêtes WTF |

#### Événements surprise en aventure idle

Se résolvent automatiquement. Le joueur voit le résultat à la reconnexion.

| ID | Nom | Effet | Fréquence |
|----|-----|-------|-----------|
| I01 | Trouvaille | Or bonus (50-200 selon la zone) | Commun (30%) |
| I02 | Rencontre Pacifique | XP bonus (+20% d'un combat normal) | Commun (25%) |
| I03 | Herbes Rares | 1-3 matériaux de craft de la zone | Peu commun (15%) |
| I04 | Piège Évité | Le héros le plus agile gagne +5% VIT pour 5 combats | Peu commun (10%) |
| I05 | Piège Pas Évité | Le héros le moins agile perd 20% PV (soignable normalement) | Peu commun (8%) |
| I06 | Rumeur de Taverne | Indice sur un coffre caché dans la zone (bonus loot prochain donjon) | Rare (5%) |
| I07 | Animal Égaré | Un animal suit le groupe → +5% chance de loot pendant 20 combats | Rare (4%) |
| I08 | Inscription Ancienne | Découverte de lore → +3 réputation dans la zone | Peu commun (10%) |
| I09 | Météorite | Matériau rare trouvé (Cristal Brut ou mieux) | Rare (2%) |
| I10 | Le Narrateur S'Ennuie | Le Narrateur invente un mini-événement absurde. Effet aléatoire mineur | Peu commun (6%) |

---

## 5. Structure d'une quête à embranchements

### 5.1 — Anatomie d'une étape

```json
{
  "step_id": 1,
  "narration": "Vous arrivez devant un pont gardé par un troll en costume-cravate.",
  "narrator_comment": "Un troll syndicaliste. Votre journée ne pouvait pas aller plus mal. Ah si, il pleut.",
  "choices": [
    {
      "id": "A",
      "text": "Montrer une fausse carte de membre",
      "test": {
        "stat": "CHA",
        "difficulty": 40,
        "trait_bonus": { "Mythomane": 20 },
        "trait_malus": { "Pacifiste": -10 }
      },
      "success": {
        "next_step": 3,
        "effects": [{ "type": "buff", "id": "B06", "target": "party" }],
        "narration": "Le troll vous laisse passer. Il n'a pas vérifié."
      },
      "failure": {
        "next_step": 2,
        "effects": [{ "type": "debuff", "id": "D05", "target": "leader" }],
        "narration": "C'est un menu de kebab. Le troll n'est pas content."
      }
    },
    {
      "id": "B",
      "text": "Payer le péage (300 or)",
      "test": null,
      "success": {
        "next_step": 4,
        "effects": [{ "type": "gold", "amount": -300 }],
        "narration": "Vous payez. Le troll vous donne un reçu. En triple exemplaire."
      },
      "failure": null
    },
    {
      "id": "C",
      "text": "Attaquer le troll",
      "test": {
        "type": "combat",
        "enemy": "troll_syndicaliste_lv15"
      },
      "success": {
        "next_step": 5,
        "effects": [
          { "type": "buff", "id": "B02", "target": "attacker" },
          { "type": "loot", "rarity_min": "peu_commun" },
          { "type": "reputation", "zone": "marais", "amount": -3 }
        ],
        "narration": "Le troll est vaincu. Son syndicat va déposer une plainte."
      },
      "failure": {
        "next_step": 6,
        "effects": [{ "type": "debuff", "id": "D03", "target": "party" }],
        "narration": "Le troll vous a mis une raclée. Avec son tampon encreur."
      }
    }
  ],
  "surprise_eligible": true
}
```

### 5.2 — Tests de stats (résolution des choix)

```
Certains choix nécessitent un test de stat.

Résolution :
  Score = Stat_du_héros + random(1, 20)
  Bonus/malus de trait appliqués
  Bonus/malus de réputation de zone appliqués (+1 par tranche de 25 réputation)
  
  Si Score >= Difficulté → Succès
  Sinon → Échec

Seuils de difficulté :
  Facile    : 20-30 (presque tout le monde réussit)
  Normal    : 35-50 (besoin d'un bon héros ou de chance)
  Difficile : 55-70 (spécialistes ou très chanceux)
  Épique    : 75-90 (stats élevées requises + chance)
  WTF       : 95+    (quasi impossible sans buffs)

Le joueur choisit QUEL HÉROS fait le test (si pertinent).
  → Un test de CHA sera mieux fait par le Barde
  → Un test d'ATQ sera mieux fait par le Barbare
  → Le bon choix de héros fait partie de la stratégie
```

### 5.3 — Tests de combat en quête

```
Quand un choix mène à un combat :
  1. Le combat se déroule normalement (système de combat standard)
  2. Mais c'est un combat SCÉNARISÉ :
     - Ennemi spécifique à la quête (stats pré-définies)
     - Pas de fuite possible (sauf compétence/objet)
     - Victoire = branche "succès"
     - Défaite = branche "échec" (pas de game over, la quête continue)
  3. Les héros K.O. en combat de quête reviennent à 1 PV après le combat
     - Ils ne sont PAS perdus
     - Mais ils ont un debuff "Blessé" (EQ02) pour les étapes suivantes
```

---

## 6. Récompenses de fin de quête

### 6.1 — Par type de quête

**Quête de zone :**

| Récompense | Quantité |
|------------|----------|
| XP | Niveau_zone × 50 × numéro_quête |
| Or | Niveau_zone × 30 × numéro_quête |
| Réputation | +QUEST_REPUTATION_PER_QUEST (10) |
| Loot | 1 objet garanti (rareté = Commun à Rare, selon la quête) |
| Effet monde | Selon la quête (M01-M10) |
| Recette | Quêtes 5 et 10 de chaque zone donnent une recette |

**Quête quotidienne :**

| Récompense | Quantité |
|------------|----------|
| XP | Niveau_joueur × 30 |
| Or | Niveau_joueur × 20 |
| Réputation | +5 dans la zone |
| Matériaux | 2-5 matériaux de la zone |
| Loot | 30% de chance d'un objet Rare |

**Quête événementielle :**

| Récompense | Quantité |
|------------|----------|
| XP | Niveau_joueur × 40 |
| Contribution boss | +500 dégâts de contribution au boss mondial |
| Jetons de Boss | 3-5 jetons |
| Buff | B12 (Aura du Héros) garanti |

**Quête WTF :**

| Récompense | Quantité |
|------------|----------|
| XP | Niveau_joueur × 100 |
| Or | Niveau_joueur × 80 |
| Loot | 1 objet Épique+ garanti |
| Buff | B07 (Inspiration Épique — long) |
| Surprise | 1 effet aléatoire parmi : recette, recrue, zone secrète, matériau rare |

### 6.2 — Récompenses bonus selon les choix

Les embranchements influencent les récompenses finales :

```
Chaque quête a un compteur caché par "voie" :

  Voie Héroïque (choix courageux, altruistes) :
    → Bonus : +25% XP, +15 réputation, buff B01
    → Le Narrateur : "Oh, un héros. C'est original."

  Voie Maligne (choix égoïstes, violents) :
    → Bonus : +25% or, +25% loot, chance objet Rare+
    → Le Narrateur : "Au moins, c'est efficace."

  Voie Comique (choix absurdes, traits exploités) :
    → Bonus : +10% toutes récompenses, événement surprise garanti
    → Le Narrateur : "...Je n'ai rien à ajouter. Bravo."

  Voie Prudente (choix sûrs, négo, fuite) :
    → Bonus : aucun debuff possible, +10% matériaux
    → Le Narrateur : "L'aventure selon vous, c'est éviter l'aventure."

Le joueur n'est PAS informé des voies pendant la quête.
Le résumé de fin montre quelle voie a dominé.
```

---

## 7. Génération de quêtes par IA (Gemini)

### 7.1 — Templates de quêtes quotidiennes

Le serveur envoie un template structuré à Gemini :

```json
{
  "template": "escort",
  "zone": "Forêt des Elfes Vexés",
  "zone_npcs": ["Elara l'Elfe Vexée", "Garde forestier"],
  "team": [
    { "name": "Gruntak", "race": "Orc", "class": "Barbare", "trait": "Pyromane" },
    { "name": "Fizzle", "race": "Gobelin", "class": "Mage", "trait": "Narcoleptique" }
  ],
  "difficulty": "normal",
  "steps": 4,
  "tone": "fantasy humoristique, style Naheulbeuk/Kaamelott, absurde",
  "constraints": {
    "choices_per_step": 3,
    "must_include_combat": true,
    "must_include_stat_test": true,
    "must_reference_traits": true,
    "no_permanent_negative_effects": true
  },
  "output_format": "JSON — structure identique à section 5.1"
}
```

### 7.2 — Validation côté serveur

```
Après réception de la quête générée par Gemini :

1. Vérifier la structure JSON (toutes les clés requises)
2. Vérifier que les effets sont dans le pool autorisé (B01-B15, D01-D10, etc.)
3. Vérifier qu'aucun effet permanent négatif n'est présent
4. Vérifier que les durées de debuffs <= QUEST_DEBUFF_DURATION_MAX
5. Vérifier que les tests de stats ont des difficultés cohérentes
6. Si invalide → fallback sur un template statique pré-écrit

La validation est CRITIQUE — on ne fait jamais confiance à une sortie IA
pour les mécaniques de jeu sans vérification.
```

### 7.3 — Cache et pool de quêtes

```
Pour éviter les appels API excessifs :

1. Pré-générer un pool de 50 quêtes quotidiennes par zone
   → Stockées en base de données
   → Rotation aléatoire parmi le pool
   → Gemini est appelé en batch (cron nocturne) pour remplir le pool

2. Quand le pool descend sous 20 quêtes pour une zone → régénérer

3. Fallback : si le pool est vide ET Gemini indisponible
   → 30 templates statiques par zone (écrits manuellement)
```

---

## 8. Aventures idle — Pool d'événements par zone

Chaque zone a son pool de micro-événements thématiques :

### Prairie des Débutants

| Événement | Effet |
|-----------|-------|
| "Une vache vous regarde avec insistance." | XP bonus (+10) |
| "Vous trouvez une pièce d'or dans l'herbe." | +15 or |
| "Un lapin agressif attaque votre sac." | -1 potion (si disponible) sinon rien |
| "Le fermier du coin vous offre du pain." | Soin mineur (10% PV max) |
| "Vous marchez dans une flaque. Votre dignité en souffre." | Aucun (narratif) |

### Forêt des Elfes Vexés

| Événement | Effet |
|-----------|-------|
| "Un elfe vous regarde avec dédain. Comme d'habitude." | +2 réputation (il vous a remarqué) |
| "Vous trouvez un arc brisé. Les elfes ont vraiment du mal." | +1 Ferraille |
| "Une fée vous lance de la poussière. Ça pique les yeux." | Debuff mineur (-5% VIT, 3 combats) |
| "Vous découvrez un arbre sculpté. C'est joli." | +1 Essence Mineure |
| "Un écureuil vous vole une noisette. Vous n'aviez pas de noisette." | Aucun (narratif, le Narrateur est confus) |

### Mines du Nain Ivre

| Événement | Effet |
|-----------|-------|
| "Thorin vous offre une chope. Pas le choix, c'est impoli de refuser." | Buff Festin (B08) 5 combats OU debuff Gueule de Bois (D02) 3 combats — 50/50 |
| "Vous trouvez une veine de minerai." | +2-4 Ferraille |
| "Un éboulement mineur. Un caillou tombe sur le casque du héros de tête." | -5% PV du héros le plus en avant (cosmétique) |
| "Un nain vous propose un pari. Pile ou face." | 50% : +100 or / 50% : -50 or |
| "Quelqu'un a laissé une pioche enchantée. Elle vibre bizarrement." | +1 Gemme Brute |

> Chaque zone a 15-20 événements idle. Le système pioche aléatoirement en évitant les répétitions récentes.

---

## 9. Résumé des types d'effets

| Catégorie | Nombre d'effets | Permanent ? | Négatif possible ? |
|-----------|-----------------|-------------|-------------------|
| Buffs héros | 15 | Non (durée en combats) | Non |
| Debuffs héros | 10 | Non (durée max 20 combats) | Oui, temporaire |
| Effets équipe | 8 | Non (durée limitée) | Oui, temporaire et récupérable |
| Effets monde | 10 | Certains oui (positifs) | Temporaire si négatif |
| Réputation | 2 axes (zone + PNJ) | Oui (cumulative, ne descend pas sous 0) | Perte temporaire uniquement |
| Événements surprise (quête) | 15 | Non | Mixtes, toujours temporaire |
| Événements surprise (idle) | 10 par zone | Non | Mineurs |

---

## 10. Constantes additionnelles pour la table `game_settings`

| Clé | Valeur par défaut | Description |
|-----|-------------------|-------------|
| `REPUTATION_MAX` | 200 | Score de réputation maximum par zone |
| `NPC_RELATION_MAX` | 100 | Score de relation maximum par PNJ |
| `NPC_GIFT_MAX_PER_DAY` | 1 | Cadeaux maximum par PNJ par jour |
| `NPC_GIFT_RELATION_GAIN` | 5 | Points de relation par cadeau |
| `QUEST_WTF_DAILY_CHANCE` | 5 | % de chance par jour d'apparition d'une quête WTF |
| `IDLE_EVENT_INTERVAL` | 30 | Minutes entre chaque micro-événement idle |
| `IDLE_EVENT_REPEAT_PROTECTION` | 5 | Nombre d'événements uniques avant qu'un puisse se répéter |
| `QUEST_VOICE_HEROIC_XP_BONUS` | 25 | % bonus XP pour la voie héroïque |
| `QUEST_VOICE_CUNNING_GOLD_BONUS` | 25 | % bonus or pour la voie maligne |
| `QUEST_VOICE_COMIC_ALL_BONUS` | 10 | % bonus toutes récompenses voie comique |
| `QUEST_DEBUFF_REMOVE_COST` | 100 | Or × niveau du héros pour retirer un debuff à la Taverne |
| `QUEST_POOL_SIZE_TARGET` | 50 | Quêtes à maintenir dans le pool par zone |
| `QUEST_POOL_REFILL_THRESHOLD` | 20 | Seuil pour déclencher la régénération du pool |
