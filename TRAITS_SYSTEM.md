# 😈 Traits Négatifs — Mécaniques Détaillées

## 1. Règles générales

### Attribution

- Chaque héros reçoit **1 trait négatif aléatoire** à la création/recrutement
- Le trait est **permanent** — il ne peut pas être changé (sauf héros renvoyé et remplacé)
- Les 10 traits ont une probabilité égale (10% chacun)
- Un joueur peut avoir plusieurs héros avec le même trait

### Déclenchement

- Chaque trait a un **pourcentage de déclenchement de base**
- Le jet est effectué **une fois par tour de combat** au début de l'action du héros
- Jet : `random(1, 100)` — si le résultat est ≤ au % de déclenchement → le trait s'active
- Un trait qui se déclenche **remplace** l'action normale du héros (sauf exceptions notées)
- Les talents de la Branche du Défaut peuvent modifier le % de déclenchement et les effets

### Constantes paramétrables (table `game_settings`)

| Clé | Valeur par défaut | Description |
|-----|-------------------|-------------|
| `TRAIT_COUARD_CHANCE` | 15 | % de chance de fuir |
| `TRAIT_NARCOLEPTIQUE_CHANCE` | 10 | % de chance de s'endormir |
| `TRAIT_NARCOLEPTIQUE_DURATION` | 2 | Durée du sommeil en tours |
| `TRAIT_NARCOLEPTIQUE_WAKE_CHANCE` | 50 | % de chance de se réveiller si touché |
| `TRAIT_KLEPTOMANE_CHANCE` | 20 | % de chance de voler le loot d'un allié |
| `TRAIT_PYROMANE_CHANCE` | 20 | % de chance de mettre le feu |
| `TRAIT_PYROMANE_DAMAGE_PERCENT` | 8 | % de l'ATQ infligé en dégâts de zone |
| `TRAIT_PYROMANE_FRIENDLY_FIRE` | 1 | 1 = les alliés prennent les dégâts, 0 = non |
| `TRAIT_ALLERGIQUE_CHANCE` | 25 | % de chance de déclencher en zone magique |
| `TRAIT_ALLERGIQUE_MALUS` | 20 | % de réduction de toutes les stats en zone magique |
| `TRAIT_PHILOSOPHE_CHANCE` | 12 | % de chance de skip le tour |
| `TRAIT_GOURMAND_CHANCE` | 25 | % de chance de consommer une potion |
| `TRAIT_SUPERSTITIEUX_BLOCK_CHANCE` | 15 | % de chance de refuser un donjon |
| `TRAIT_MYTHOMANE_VARIANCE` | 20 | % de variance sur les stats affichées |
| `TRAIT_PACIFISTE_CHANCE` | 15 | % de chance de refuser d'attaquer |
| `TRAIT_PACIFISTE_THRESHOLD` | 30 | % PV de l'ennemi sous lequel il est "trop mignon" |

---

## 2. Les 10 Traits — Fiches détaillées

---

### 2.1 — COUARD

*"La fuite, c'est une stratégie."*

**En combat :**

```
Déclenchement : Début du tour du héros
Chance : TRAIT_COUARD_CHANCE (15%)
Condition : Le héros est en combat

Si déclenché :
  1. Le héros tente de fuir
  2. En idle : le héros est retiré du combat pour le reste de l'affrontement
     - Il ne subit plus de dégâts
     - Il ne peut plus agir
     - Il revient pour le prochain combat
  3. En quête active : le héros est indisponible pour 2 tours puis revient
     - "Il est allé se cacher derrière un rocher"
  4. Le Narrateur commente

Interaction avec les boss :
  - Le héros fuit mais revient au tour suivant (nulle part où aller)
  - "Il a essayé de fuir un boss dans une arène fermée. Malin."
```

**Hors combat :**
- Aucun effet

**Scaling avec le niveau :**

| Niveau | Chance | Modification |
|--------|--------|-------------|
| 1-25 | 15% | Base |
| 26-50 | 13% | Le héros "s'habitue" un peu |
| 51-75 | 11% | Légère amélioration |
| 76-100 | 10% | Minimum naturel |

**Synergies avec les classes (Branche du Défaut) :**

| Classe | Talent requis | Interaction |
|--------|---------------|-------------|
| Guerrier | Catastrophe Ambulante (C7) | La fuite fait aussi fuir l'ennemi le plus proche |
| Voleur | Échappatoire (C4) | En fuyant, vole un objet à l'ennemi |
| Voleur | Roi des Embrouilles (C7) | Fuite + effet positif aléatoire |
| Ranger | Sniper Somnambule (C7) | Tire automatiquement avant de fuir |
| Barbare | Casse Involontaire (C1) | En fuyant, le Barbare renverse tout → dégâts AoE |

**Exemples de commentaires du Narrateur :**
> "Gruntak le Courageux — pardon, le Couard — a encore fait honneur à son titre."
> "Il court vite pour quelqu'un en armure lourde. La peur, ça motive."

---

### 2.2 — NARCOLEPTIQUE

*"Zzz... Quoi ? On est attaqués ?"*

**En combat :**

```
Déclenchement : Début du tour du héros
Chance : TRAIT_NARCOLEPTIQUE_CHANCE (10%)
Condition : Le héros n'est pas déjà endormi

Si déclenché :
  1. Le héros s'endort pour TRAIT_NARCOLEPTIQUE_DURATION tours (2)
  2. Pendant le sommeil :
     - Le héros skip ses tours
     - Il peut être ciblé normalement par les ennemis
     - S'il est touché par une attaque : TRAIT_NARCOLEPTIQUE_WAKE_CHANCE (50%) de se réveiller
     - Les soins alliés ne le réveillent PAS
  3. À la fin de la durée, il se réveille automatiquement
  4. Quand il se réveille, son prochain tour a +10% VIT (bonus de repos)
```

**Hors combat :**
- Aucun effet mécanique (mais le Narrateur peut commenter qu'il dort entre les combats)

**Scaling avec le niveau :**

| Niveau | Chance | Modification |
|--------|--------|-------------|
| 1-25 | 10% | Base |
| 26-50 | 9% | Léger apprentissage |
| 51-75 | 8% | - |
| 76-100 | 7% | Minimum naturel |

**Synergies avec les classes (Branche du Défaut) :**

| Classe | Talent requis | Interaction |
|--------|---------------|-------------|
| Guerrier | Catastrophe Ambulante (C7) | S'endort → endort l'ennemi le plus proche aussi |
| Mage | Explosion Involontaire (C3) | S'endort → explosion AoE à 80% INT |
| Ranger | Sniper Somnambule (C7) | Tire avant de s'endormir → DPS gratuit |
| Ranger | Observation Passive (C4) | Skip un tour → prochain tir +40% |
| Barde | Berceuse (C5) | Berceuse endort TOUS les ennemis 1 tour au lieu d'un seul |
| Barde | Génie Incompris (C7) | Buff toute l'équipe à chaque sieste |
| Barbare | Fureur Incontrôlable (C3) | Attaque même endormi à 50% ATQ |

---

### 2.3 — KLEPTOMANE

*"C'était dans ma poche depuis le début."*

**En combat :**
- Aucun effet (le Kleptomane vole APRÈS le combat)

**Après le combat (phase de loot) :**

```
Déclenchement : Phase de distribution du loot
Chance : TRAIT_KLEPTOMANE_CHANCE (20%)
Condition : Au moins 2 héros vivants à la fin du combat

Si déclenché :
  1. Le Kleptomane "vole" l'XP bonus d'un allié aléatoire
     - L'allié perd 10% de l'XP gagnée dans ce combat
     - Le Kleptomane gagne cette XP en plus de la sienne
  2. Si un loot Rare+ est trouvé :
     - 30% de chance que le Kleptomane s'attribue l'objet
       même si ce n'est pas son slot
     - L'objet va dans l'inventaire, pas équipé automatiquement
  3. Le Narrateur accuse le Kleptomane

Cas spécial — Kleptomane seul survivant :
  - Pas de vol possible → le trait ne se déclenche pas
  - "Il a essayé de se voler lui-même. Ça n'a pas marché."
```

**Hors combat (exploration idle) :**
- +5% de chance de trouver de l'or bonus (il "trouve" des trucs qui traînent)

**Scaling avec le niveau :**

| Niveau | Chance | Modification |
|--------|--------|-------------|
| 1-25 | 20% | Base |
| 26-50 | 18% | - |
| 51-75 | 16% | - |
| 76-100 | 15% | Minimum (c'est compulsif) |

**Synergies avec les classes (Branche du Défaut) :**

| Classe | Talent requis | Interaction |
|--------|---------------|-------------|
| Voleur | Doigts Agiles (C1) | +15% loot supplémentaire s'empile avec le vol |
| Voleur | Pickpocket (C2) | Au lieu de voler un allié, vole un buff ennemi |
| Voleur | Roi des Embrouilles (C7) | Vol + effet positif aléatoire pour l'équipe |
| Barde | Auto-dérision (C4) | Chaque vol donne +10% CHA au Barde |
| Nécro | Squelette Rebelle (C1) | Le squelette vole aussi un objet à l'ennemi |

---

### 2.4 — PYROMANE

*"Le feu, ça résout tout."*

**En combat :**

```
Déclenchement : Quand le héros effectue une attaque (APRÈS le calcul de dégâts)
Chance : TRAIT_PYROMANE_CHANCE (20%)
Condition : Le héros a attaqué (pas skip, pas soin, pas buff)

Si déclenché :
  1. Le héros met le feu à la zone
  2. Dégâts de feu = ATQ × TRAIT_PYROMANE_DAMAGE_PERCENT / 100
  3. Cibles affectées :
     - Tous les ennemis (100% des dégâts de feu)
     - Si TRAIT_PYROMANE_FRIENDLY_FIRE = 1 :
       Tous les alliés SAUF le Pyromane (50% des dégâts de feu)
  4. Chaque cible touchée a 30% de chance de recevoir l'effet "En feu" (2 tours)
  5. L'attaque normale du héros est effectuée en PLUS (le feu est un bonus)
  6. Le Narrateur commente le chaos

Note : Le Pyromane lui-même est immunisé à son propre feu
  et à l'effet de statut "En feu" en général.
```

**Hors combat :**
- 5% de chance de brûler un objet Commun de l'inventaire après chaque donjon
- "Oups. Ta Dague Rouillée a fondu. Désolé."

**Scaling avec le niveau :**

| Niveau | Chance | DAMAGE_PERCENT | Modification |
|--------|--------|----------------|-------------|
| 1-25 | 20% | 8% | Base |
| 26-50 | 20% | 10% | Dégâts augmentent (il s'améliore dans sa folie) |
| 51-75 | 20% | 12% | - |
| 76-100 | 20% | 15% | Maximum pyrotechnique |

> Le Pyromane est unique : sa chance ne baisse PAS avec le niveau, mais ses dégâts AUGMENTENT. C'est le seul trait qui scale positivement.

**Synergies avec les classes (Branche du Défaut) :**

| Classe | Talent requis | Interaction |
|--------|---------------|-------------|
| Mage | Embrasement (A6) | Sorts + feu = double source d'ignition |
| Mage | Bombe à Retardement (C7) | Mort en feu → explosion massive |
| Barbare | Casse Involontaire (C1) | Feu + destruction = dégâts AoE empilés |
| Barbare | Force de la Nature (C7) | Onde de choc + "En feu" sur tous les ennemis |
| Guerrier | Catastrophe Ambulante (C7) | Met aussi le feu à l'ennemi le plus proche (dégâts concentrés) |

---

### 2.5 — ALLERGIQUE À LA MAGIE

*"ATCHOUM — ah, le boss nous a vus."*

**En combat :**

```
Déclenchement : Début de chaque tour en ZONE MAGIQUE
Chance : TRAIT_ALLERGIQUE_CHANCE (25%)
Condition : La zone actuelle est marquée comme "magique" en base de données
  Zones magiques par défaut : Tour du Mage Distrait, zones avec ennemis magiques,
  donjons enchantés

Si déclenché :
  1. Le héros éternue violemment
  2. Effets :
     - Skip le tour (l'éternuement est trop violent)
     - L'éternuement révèle la position : si le groupe avait un bonus de surprise
       ou d'embuscade, il est perdu
     - Les ennemis gagnent +10% de chance de toucher le héros pour 1 tour
  3. Si l'allergique éternue 3 fois dans le même combat :
     - Malus permanent pour le combat : toutes stats -TRAIT_ALLERGIQUE_MALUS (20%)
     - "Son nez coule tellement qu'il ne voit plus rien."

En zone NON magique : Le trait ne se déclenche JAMAIS.
  C'est un trait contextuel — très pénalisant dans certaines zones,
  totalement inoffensif dans d'autres.
```

**Hors combat :**
- Les potions magiques ont 15% de chance de ne pas fonctionner sur ce héros
- "Il est allergique. La potion l'a fait gonfler au lieu de le soigner."

**Scaling avec le niveau :**

| Niveau | Chance | Modification |
|--------|--------|-------------|
| 1-25 | 25% | Base |
| 26-50 | 22% | Antihistaminiques médiévaux |
| 51-75 | 20% | - |
| 76-100 | 18% | Minimum (l'allergie ne part jamais vraiment) |

**Synergies avec les classes (Branche du Défaut) :**

| Classe | Talent requis | Interaction |
|--------|---------------|-------------|
| Mage | Siphon d'Erreur (C2) | Éternuement → récupère 10% PV |
| Mage | Explosion Involontaire (C3) | Éternuement = explosion AoE magique |
| Prêtre | Prière Confuse (C1) | L'allergie transforme les soins en dégâts sur un ennemi |
| Ranger | Instinct (C2) | Éternuement → +20% VIT (l'adrénaline de la honte) |
| Barbare | Adrénaline (C2) | Éternuement → VIT doublée 1 tour |

**Note de design :** Ce trait est volontairement situationnel. Un héros allergique est parfaitement viable dans les zones physiques (Mines, Prairie, Marais) mais galère dans les zones magiques. Ça encourage le joueur à adapter son équipe selon la zone.

---

### 2.6 — PHILOSOPHE

*"Mais au fond, pourquoi combattre ?"*

**En combat :**

```
Déclenchement : Début du tour du héros
Chance : TRAIT_PHILOSOPHE_CHANCE (12%)
Condition : Le héros est en combat

Si déclenché :
  1. Le héros s'arrête pour réfléchir au sens de la vie
  2. Skip le tour complet
  3. MAIS : le héros gagne un buff "Illumination"
     - +5% INT permanent pour le reste du combat
     - Cumulable (chaque philosophie = +5% de plus)
  4. Le Narrateur cite une fausse réflexion philosophique absurde
     (générée par Gemini si disponible)

Exemples de réflexions :
  "Si un arbre tombe dans une forêt et qu'un Nain est là,
   est-ce que ça fait du bois de chauffage ?"
  "Je frappe, donc je suis. Mais suis-je vraiment ?"
```

**Hors combat :**
- +5% XP gagnée (le héros "réfléchit" à ses expériences)
- C'est le seul trait avec un bonus permanent hors combat

**Scaling avec le niveau :**

| Niveau | Chance | INT bonus par philosophie | Modification |
|--------|--------|--------------------------|-------------|
| 1-25 | 12% | +5% | Base |
| 26-50 | 12% | +6% | Plus profond |
| 51-75 | 11% | +7% | - |
| 76-100 | 10% | +8% | Maximum de sagesse |

> Le Philosophe est unique : sa chance baisse légèrement mais le bonus par déclenchement AUGMENTE. C'est un investissement à long terme dans le combat.

**Synergies avec les classes (Branche du Défaut) :**

| Classe | Talent requis | Interaction |
|--------|---------------|-------------|
| Prêtre | Hérétique Sacré (C7) | Skip + buff Inspiré toute l'équipe |
| Prêtre | Crise Mystique (C4) | 50% de chance de Révélation → prochain sort gratuit |
| Nécro | Maître de l'Erreur (C7) | Skip + invoque un mort-vivant gratuit |
| Nécro | Chaos Nécrotique (C6) | Skip + crée un squelette gratuit |
| Barde | Génie Incompris (C7) | Skip + buff aléatoire toute l'équipe |
| Mage | Éruption de Trait (C6) | Skip + INT +8% cumulable |

---

### 2.7 — GOURMAND

*"C'était pas du jus de pomme ?"*

**En combat :**

```
Déclenchement : Début du tour du héros
Chance : TRAIT_GOURMAND_CHANCE (25%)
Condition : L'équipe possède au moins une potion de soin dans l'inventaire de combat

Si déclenché :
  1. Le héros consomme automatiquement une potion de soin
  2. La potion est consommée même si le héros est à PV max
  3. Si PV max → la potion est gaspillée entièrement
  4. Si le héros n'est PAS à PV max → il reçoit les soins normalement
     ET il effectue son action normale (le grignotage ne prend pas le tour)
  5. Si PV max ET potion gaspillée → il perd son tour à manger
  6. Si aucune potion disponible → le trait ne se déclenche pas
     - "Il cherche un truc à manger. Il n'y a rien. Il boude."
     - Le héros agit normalement mais avec -5% ATQ ce tour (bouderie)

Gestion de l'inventaire de combat :
  - Le joueur assigne X potions avant chaque donjon/quête
  - En idle, un stock auto de potions est géré par le système
  - Le Gourmand réduit le stock plus vite
```

**Hors combat :**
- Le coût en or de la Taverne est +20% pour ce héros (il commande double)
- Pas d'effet mécanique sinon

**Scaling avec le niveau :**

| Niveau | Chance | Modification |
|--------|--------|-------------|
| 1-25 | 25% | Base |
| 26-50 | 22% | Apprend un peu la retenue |
| 51-75 | 20% | - |
| 76-100 | 18% | Toujours gourmand mais gérable |

**Synergies avec les classes (Branche du Défaut) :**

| Classe | Talent requis | Interaction |
|--------|---------------|-------------|
| Ranger | Herboristerie (B3) | Les potions gaspillées soignent quand même 30% de leur valeur |
| Prêtre | Foi Aveugle (C3) | Potion gaspillée → soin aléatoire sur un allié (la potion "rebondit") |
| Barbare | Soif de Sang (A3) | Consommer une potion donne +10% ATQ 2 tours (sucre = énergie) |
| Guerrier | Tête Dure (C4) | La bouderie dure 0 tour au lieu de 1 |

**Note de design :** Le Gourmand est un drain de ressources, pas un malus de combat direct. Il te force à gérer tes potions plus attentivement ou à investir plus d'or. C'est un trait "économique".

---

### 2.8 — SUPERSTITIEUX

*"Pas un mardi ! Jamais un mardi !"*

**En combat :**
- Aucun effet direct en combat

**Hors combat (entrée de donjon) :**

```
Déclenchement : Quand le joueur lance un donjon spécial ou une quête
Chance : TRAIT_SUPERSTITIEUX_BLOCK_CHANCE (15%)
Condition : Le héros Superstitieux est dans l'équipe assignée

Si déclenché :
  1. Le héros refuse d'entrer dans le donjon
  2. Raison aléatoire générée :
     - "Un chat noir a traversé devant l'entrée."
     - "La porte s'ouvre vers la gauche. Mauvais présage."
     - "Il y a 13 marches. TREIZE."
     - "On est un mardi." / "La lune est en phase descendante."
  3. Options pour le joueur :
     a) Entrer sans lui (équipe réduite de 1 membre)
     b) Attendre et réessayer (nouveau jet dans 1 heure)
     c) Le "convaincre" en payant 100 or × niveau du héros
        → Réussite automatique mais le héros a -10% toutes stats
        dans ce donjon (il est nerveux)
  
Cas spécial — Le héros est le SEUL de l'équipe :
  - Le joueur DOIT payer ou attendre
  - "Tu vas quand même pas y aller tout seul ET un mardi ?"
```

**En exploration idle :**
- Aucun effet (il râle mais suit le groupe)

**Scaling avec le niveau :**

| Niveau | Chance | Coût de conviction | Modification |
|--------|--------|-------------------|-------------|
| 1-25 | 15% | 100 × niveau | Base |
| 26-50 | 13% | 80 × niveau | Un peu plus raisonnable |
| 51-75 | 12% | 60 × niveau | - |
| 76-100 | 10% | 50 × niveau | Toujours superstitieux mais négociable |

**Synergies avec les classes (Branche du Défaut) :**

| Classe | Talent requis | Interaction |
|--------|---------------|-------------|
| Barde | Bis Repetita (C2) | Si le Barde est dans l'équipe, il convainc le Superstitieux gratuitement (50% de chance) |
| Voleur | Baratineur (C5) | Le Voleur peut convaincre sans payer (40% de chance) |
| Prêtre | Bénédiction (A1) | Le Prêtre "bénit" l'entrée → -5% de chance de blocage |

**Note de design :** Seul trait qui n'affecte pas le combat mais la logistique. Il est frustrant mais gérable, et crée des moments narratifs drôles. C'est un gold sink déguisé.

---

### 2.9 — MYTHOMANE

*"J'ai déjà tué un dragon. Enfin, un gros lézard."*

**En combat :**
- Aucun effet mécanique direct sur le combat

**En affichage (UI) :**

```
Application : En permanence sur l'écran du joueur
Condition : Le héros Mythomane est dans l'équipe

Effet :
  1. Les stats AFFICHÉES du Mythomane sont fausses
  2. Chaque stat est modifiée de ±TRAIT_MYTHOMANE_VARIANCE (20%)
  3. La direction (+ ou -) est aléatoire par stat et change à chaque
     actualisation de l'écran (toutes les 60 secondes)
  4. Les stats RÉELLES sont utilisées pour les calculs de combat
  5. Le joueur ne voit JAMAIS les vraies stats dans l'UI normale

Comment voir les vraies stats :
  - Un bouton "Vérifier les stats" avec un cooldown de 30 minutes
  - Dépenser 50 or pour un "Audit de compétences"
  - Les logs de combat montrent les vrais chiffres

Impact sur les décisions :
  - Le joueur ne sait pas exactement si son Mythomane est plus fort
    ou plus faible qu'affiché
  - Ça rend l'optimisation de l'équipement plus incertaine
  - Les comparaisons d'objets sont trompeuses
```

**Hors combat :**
- Les stats affichées dans le profil et le classement sont aussi fausses
- "Selon lui, il a 847 en ATQ. En vrai, c'est 423."

**Scaling avec le niveau :**

| Niveau | Variance | Modification |
|--------|----------|-------------|
| 1-25 | ±20% | Base |
| 26-50 | ±18% | Un peu moins menteur |
| 51-75 | ±15% | - |
| 76-100 | ±12% | Toujours menteur mais moins extrême |

**Synergies avec les classes (Branche du Défaut) :**

| Classe | Talent requis | Interaction |
|--------|---------------|-------------|
| Barde | Public Captif (C1) | Les ennemis "croient" les stats gonflées → -10% ATQ ennemi si les stats affichées du Mythomane sont supérieures aux vraies |
| Voleur | Baratineur (C5) | La confusion des stats rend le Voleur plus difficile à cibler → +5% esquive |
| Nécro | Énergie Résiduelle (C2) | Les fausses stats "inspirent" les invocations → +10% stats des squelettes |

**Note de design :** C'est le seul trait qui affecte le JOUEUR et pas le héros. Il ajoute une couche d'incertitude amusante et unique. Le joueur doit apprendre à jouer avec l'imprécision. Mécaniquement, le héros est parfaitement normal — c'est l'information qui est corrompue.

---

### 2.10 — PACIFISTE

*"Non mais regarde sa petite tête !"*

**En combat :**

```
Déclenchement : Quand le héros devrait attaquer un ennemi
Chance : TRAIT_PACIFISTE_CHANCE (15%)
Condition supplémentaire : L'ennemi ciblé est sous TRAIT_PACIFISTE_THRESHOLD (30%) PV

Si les deux conditions sont remplies :
  1. Le héros refuse d'attaquer ("Il est blessé, je peux pas lui faire ça...")
  2. Au lieu d'attaquer, le héros fait UNE des actions suivantes (aléatoire) :
     a) Défend (DEF +20% pour 1 tour) — 40%
     b) Encourage un allié (un allié aléatoire gagne +10% ATQ 1 tour) — 30%
     c) Tente de "soigner" l'ennemi (soin de 5% PV max ennemi) — 20%
     d) Ne fait rien (contemple la scène) — 10%
  3. Le Narrateur commente l'absurdité

Si l'ennemi est au-dessus de 30% PV :
  - Le trait ne se déclenche JAMAIS
  - Le héros combat normalement

Cas spécial — Boss :
  - Le Pacifiste peut quand même refuser d'attaquer un boss blessé
  - "Il refuse de frapper le dragon. LE DRAGON."
  - Le cas (c) soigner l'ennemi est particulièrement pénalisant sur un boss
```

**Hors combat :**
- Ce héros ne peut pas "renvoyer" un autre héros de l'équipe
- "Il refuse de virer quelqu'un. C'est trop méchant."
- (Impact cosmétique, le joueur peut renvoyer via le menu directement)

**Scaling avec le niveau :**

| Niveau | Chance | Threshold | Modification |
|--------|--------|-----------|-------------|
| 1-25 | 15% | 30% PV | Base |
| 26-50 | 13% | 28% PV | Un peu plus pragmatique |
| 51-75 | 12% | 25% PV | - |
| 76-100 | 10% | 20% PV | Toujours sensible mais seuil plus bas |

**Synergies avec les classes (Branche du Défaut) :**

| Classe | Talent requis | Interaction |
|--------|---------------|-------------|
| Prêtre | Prière Confuse (C1) | Le refus de frapper lance un soin sur un allié au lieu de rien |
| Prêtre | Hérétique Sacré (C7) | Le refus buff toute l'équipe en Inspiré |
| Barde | Hymne de Guerre (A2) | L'encouragement (option b) affecte TOUTE l'équipe au lieu d'un allié |
| Guerrier | Maladresse Calculée (C1) | Le refus donne +15% ATQ au Guerrier pour le prochain tour ("Bon, j'y vais moi") |

---

## 3. Interactions entre traits et talents — Règles de priorité

### 3.1 — Ordre de résolution

```
1. Début du tour du héros
2. Vérification des effets de statut (étourdi, endormi, etc.)
   → Si étourdi/endormi : skip. Le trait ne se vérifie PAS (sauf exceptions)
3. Jet de trait négatif
   → Si déclenché : appliquer l'effet du trait
   → PUIS vérifier les talents de la Branche du Défaut
   → Les talents peuvent modifier, annuler ou amplifier l'effet
4. Si le trait n'a pas skip le tour : action normale du héros
5. Effets post-action (Pyromane, etc.)
```

### 3.2 — Exceptions

| Situation | Règle |
|-----------|-------|
| Héros étourdi + trait | Le trait ne se vérifie pas (l'étourdissement prime) |
| Héros endormi + trait | Le trait ne se vérifie pas SAUF si un talent dit le contraire (ex: Fureur Incontrôlable du Barbare) |
| Deux effets contradictoires | Le talent de la Branche du Défaut a priorité sur l'effet négatif du trait |
| Trait réduit à 0% | Si des talents réduisent le % à 0 ou moins, le trait ne se déclenche plus jamais |
| Trait augmenté au-delà de 100% | Plafonné à 50% (le trait ne peut pas se déclencher plus d'une fois sur deux) |

### 3.3 — Traits et combat offline

En simulation offline (calcul à la reconnexion), les traits sont simulés de façon simplifiée :

```
Impact_trait = Chance_déclenchement × Pénalité_moyenne / 100

Puissance_effective = Puissance_héros × (100 - Impact_trait) / 100

Exemples :
  Couard (15%) : perd ~15% de ses tours → Puissance × 85 / 100
  Narcoleptique (10%, 2 tours) : perd ~20% de ses tours → Puissance × 80 / 100
  Pyromane (20%) : BONUS car fait des dégâts AoE → Puissance × 110 / 100
  Philosophe (12%) : perd des tours mais gagne INT → Puissance × 95 / 100
  Kleptomane : pas d'impact combat → Puissance × 100 / 100
  Superstitieux : pas d'impact combat → Puissance × 100 / 100
  Mythomane : pas d'impact combat → Puissance × 100 / 100
```

> Les talents de la Branche du Défaut sont aussi pris en compte pour ajuster l'impact positif/négatif du trait.

---

## 4. Tableau récapitulatif

| Trait | Chance base | Quand | Effet principal | Scaling | Impact hors combat |
|-------|-------------|-------|-----------------|---------|-------------------|
| Couard | 15% | Tour de combat | Fuit le combat | Chance ↓ | Aucun |
| Narcoleptique | 10% | Tour de combat | S'endort 2 tours | Chance ↓ | Aucun |
| Kleptomane | 20% | Après combat (loot) | Vole XP/loot des alliés | Chance ↓ | +5% or trouvé |
| Pyromane | 20% | Après une attaque | Dégâts de zone (alliés inclus) | Dégâts ↑ | 5% brûler un objet |
| Allergique | 25% | Tour en zone magique | Skip tour + malus | Chance ↓ | Potions 15% d'échec |
| Philosophe | 12% | Tour de combat | Skip tour + buff INT | INT bonus ↑ | +5% XP |
| Gourmand | 25% | Tour de combat | Consomme une potion | Chance ↓ | +20% coût taverne |
| Superstitieux | 15% | Entrée de donjon | Refuse d'entrer | Chance ↓ / coût ↓ | Aucun |
| Mythomane | - | Permanent (UI) | Stats affichées fausses | Variance ↓ | Stats profil fausses |
| Pacifiste | 15% | Attaque ennemi < 30% PV | Refuse d'attaquer | Chance ↓ / seuil ↓ | Cosmétique |
