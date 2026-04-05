# 💰 Système Économique — Or, Matériaux & Boutiques

## 1. Règle fondamentale

> **L'or est la seule monnaie du jeu.** Pas de gemmes, pas de jetons, pas de monnaie premium. Tout s'achète, se répare, se craft et se débloque avec de l'or. Les matériaux sont des composants de craft, pas des monnaies.

---

## 2. Constantes paramétrables (table `game_settings`)

### 2.1 — Or

| Clé | Valeur par défaut | Description |
|-----|-------------------|-------------|
| `GOLD_PER_KILL_BASE` | 5 | Or de base par monstre tué |
| `GOLD_PER_KILL_LEVEL_MULT` | 2 | Or supplémentaire par niveau du monstre |
| `GOLD_ELITE_BONUS` | 50 | % de bonus or pour un monstre élite |
| `GOLD_MINIBOSS_MULT` | 5 | Multiplicateur or pour un mini-boss |
| `GOLD_BOSS_MULT` | 15 | Multiplicateur or pour un boss de zone |
| `GOLD_QUEST_DAILY_MULT` | 20 | Or = niveau_joueur × ce multiplicateur (quêtes quotidiennes) |
| `GOLD_QUEST_ZONE_MULT` | 30 | Or = niveau_zone × ce mult × numéro_quête |
| `GOLD_OFFLINE_EFFICIENCY` | 75 | % de l'or normal gagné en idle offline |
| `GOLD_SELL_PERCENT` | 30 | % de la valeur de base récupéré à la vente |
| `SHOP_REFRESH_HOURS` | 6 | Heures entre chaque refresh de la boutique |
| `SHOP_ITEMS_COUNT` | 6 | Nombre d'objets affichés par boutique |
| `SHOP_PRICE_MARKUP` | 300 | % du prix de base (les boutiques vendent cher) |

### 2.2 — Matériaux

| Clé | Valeur par défaut | Description |
|-----|-------------------|-------------|
| `MATERIAL_DROP_CHANCE` | 30 | % de chance de drop de matériau par combat |
| `MATERIAL_ELITE_BONUS` | 100 | % de chance bonus pour les élites (30+30=60%) |
| `MATERIAL_BOSS_GUARANTEED` | 3 | Nombre de matériaux garantis par boss |
| `MATERIAL_RARE_CHANCE` | 5 | % de chance de matériau rare cross-zone par combat |

---

## 3. Sources d'or (entrées)

### 3.1 — Tableau complet des sources

| Source | Formule | Exemple (niv. 30) | Fréquence |
|--------|---------|-------------------|-----------|
| Kill monstre normal | `GOLD_PER_KILL_BASE + (Niv_monstre × GOLD_PER_KILL_LEVEL_MULT)` | 5 + (30 × 2) = 65 or | Chaque combat |
| Kill monstre élite | Même formule × (100 + GOLD_ELITE_BONUS) / 100 | 65 × 150 / 100 = 97 or | ~8% des combats |
| Kill mini-boss | Formule × GOLD_MINIBOSS_MULT | 65 × 5 = 325 or | Donjons |
| Kill boss de zone | Formule × GOLD_BOSS_MULT | 65 × 15 = 975 or | 1×/zone |
| Quête quotidienne | Niv_joueur × GOLD_QUEST_DAILY_MULT | 30 × 20 = 600 or | 3/jour |
| Quête de zone | Niv_zone × GOLD_QUEST_ZONE_MULT × numéro_quête | 30 × 30 × 5 = 4500 or | ~10/zone |
| Quête WTF | Niv_joueur × 80 | 30 × 80 = 2400 or | Rare |
| Vente d'objets | Niv_objet × Base_rareté × GOLD_SELL_PERCENT / 100 | Voir section loot | Fréquent |
| Événement idle | 50-200 or selon la zone | ~100 or | ~2/heure |
| Boss mondial (participation) | Contribution_% × 1000 | Variable | 1/3 jours |
| Idle offline | Or_combat × GOLD_OFFLINE_EFFICIENCY / 100 | 65 × 75 / 100 = 48 or/combat | Passif |

### 3.2 — Estimation de revenus par heure (niveau 30, zone 4)

```
Combats idle par heure : ~30 (1 toutes les 2 minutes)
Or par combat moyen : 65 (normal) + ~8 (élites moyennés) = 73
Or idle par heure : 73 × 30 = 2190 or

Événements idle : ~2/heure × 100 = 200 or
Matériaux vendables : ~300 or

Total estimé par heure (online) : ~2700 or
Total estimé par heure (offline) : ~2000 or (75% efficacité)

Quêtes quotidiennes (3) : 600 × 3 = 1800 or
Donjon spécial (1/8h) : ~500 or

Revenu journalier estimé (joueur actif) : ~25000-35000 or
```

---

## 4. Dépenses d'or (sorties / sinks)

### 4.1 — Tableau complet des dépenses

| Dépense | Formule | Exemple (niv. 30) | Fréquence |
|---------|---------|-------------------|-----------|
| **Recrutement héros** | Slot × 1000 (coût croissant par slot) | Slot 3 : 3000 or | 1-4 fois total |
| **Boutique (acheter équipement)** | Prix_base × SHOP_PRICE_MARKUP / 100 | Rare niv.30 : 3600 or | Au choix |
| **Réparation** | (Dura_max - Dura_actuelle) × Niv_objet × 2 | 60 pts × 30 × 2 = 3600 or | Régulier |
| **Crafting (fusion)** | Niv_moyen × 50 × Mult_rareté | 3 Rares niv.30 : 30 × 50 × 4 = 6000 or | Au choix |
| **Reset talents** | Base 500, ×150% à chaque reset | 1er: 500, 2e: 750, 3e: 1125... | Rare |
| **Convaincre Superstitieux** | 100 × Niv_héros (décroît avec niveau) | 100 × 30 = 3000 or | Situationnel |
| **Retirer debuff (Taverne)** | 100 × Niv_héros | 100 × 30 = 3000 or | Situationnel |
| **Audit stats Mythomane** | 50 or fixe | 50 or | Situationnel |
| **Recettes (matériaux + or)** | Variable par recette | 80-5000 or | Au choix |
| **Expansion inventaire** | 500 par tranche de 20 slots | 500 or | 1-5 fois |
| **Cadeaux PNJ** | 200 × Niv_zone | 200 × 30 = 6000 or | 1/jour/PNJ |
| **Enchantement (boutique)** | Voir section 6 | 2000-15000 or | Au choix |
| **Gourmand (surcoût Taverne)** | +20% sur les prix Taverne | Variable | Passif (trait) |

### 4.2 — Équilibre revenus/dépenses

```
Objectif : Le joueur doit toujours avoir des choix de dépenses intéressants
sans être en faillite permanente.

Ratio cible : Le joueur devrait pouvoir se permettre 60-70% de ce qu'il
veut dans une journée, ce qui force des choix et de la priorisation.

À niveau 30, revenu journalier ~30000 or :
  - Réparations quotidiennes : ~5000 or (entretien de 5 objets)
  - 1 achat boutique Rare : ~3600 or
  - 1 fusion : ~6000 or
  - Potions et consommables : ~2000 or
  - Total dépenses "confort" : ~16600 or
  - Reste pour épargner : ~13400 or
  
  → Un objet Épique en boutique coûte ~12000 or = 1 jour d'épargne
  → Un reset de talents (1er) = ~30 minutes de farm
  → Un cadeau PNJ = ~3 heures de farm
  → C'est la bonne tension.
```

---

## 5. Boutiques

### 5.1 — Boutique de zone

Chaque zone a une boutique qui vend des objets thématiques. Le stock se renouvelle toutes les `SHOP_REFRESH_HOURS` (6 heures).

```
Génération du stock :
  1. Tirer SHOP_ITEMS_COUNT (6) objets
  2. Raretés : 2 Communs, 2 Peu communs, 1 Rare, 1 Épique (si zone niv. 20+)
  3. Niveaux : Niveau_zone ± 3
  4. Slots : aléatoire mais au moins 1 Arme et 1 Armure garantis
  5. Les objets sont du pool de la zone (thématiques)
  6. Prix = Prix_base_rareté × Niv_objet × SHOP_PRICE_MARKUP / 100
```

**Prix de base par rareté (avant markup) :**

| Rareté | Prix de base par niveau |
|--------|----------------------|
| Commun | 10 × Niveau |
| Peu commun | 25 × Niveau |
| Rare | 60 × Niveau |
| Épique | 150 × Niveau |
| Légendaire | 500 × Niveau |

**Avec SHOP_PRICE_MARKUP (300%) :**

| Rareté | Prix final (niv. 30) |
|--------|---------------------|
| Commun | 10 × 30 × 300 / 100 = 900 or |
| Peu commun | 25 × 30 × 300 / 100 = 2250 or |
| Rare | 60 × 30 × 300 / 100 = 5400 or |
| Épique | 150 × 30 × 300 / 100 = 13500 or |
| Légendaire | 500 × 30 × 300 / 100 = 45000 or |

> Les Légendaires n'apparaissent JAMAIS en boutique de zone. Ils se trouvent uniquement en loot, crafting, boss ou quêtes WTF.

### 5.2 — Boutique de la Taverne (consommables)

Disponible partout, stock illimité sur les basiques.

| Objet | Prix | Effet |
|-------|------|-------|
| Potion de Soin Mineure | 30 × Niv_zone | Soigne 20% PV max |
| Potion de Soin | 80 × Niv_zone | Soigne 40% PV max |
| Potion de Soin Majeure | 200 × Niv_zone | Soigne 70% PV max |
| Parchemin de Fuite | 50 × Niv_zone | Fuite garantie (1 utilisation) |
| Pierre d'Aiguisage | 100 × Niv_zone | +10% ATQ pour 5 combats |
| Encens de Protection | 100 × Niv_zone | +10% DEF pour 5 combats |
| Élixir de Vitesse | 120 × Niv_zone | +15% VIT pour 5 combats |
| Antidote | 60 × Niv_zone | Retire Empoisonné |
| Kit de Réparation Basique | 150 × Niv_zone | Répare 20 durabilité |
| Kit de Réparation Avancé | 400 × Niv_zone | Répare 50 durabilité |
| Torche Éternelle | 200 × Niv_zone | Évite le debuff "Explorer sans torche" en donjon |

### 5.3 — Boutique de Gérard (crafting)

Gérard vend aussi des matériaux de base à prix élevé pour dépanner.

| Matériau | Prix | Stock par refresh |
|----------|------|-------------------|
| Ferraille | 50 or | 20 |
| Cuir | 80 or | 15 |
| Gemme Brute | 100 or | 10 |
| Essence Mineure | 200 or | 8 |
| Cristal Brut | 500 or | 5 |
| Essence Majeure | 1500 or | 2 |
| Fragment Stellaire | 5000 or | 1 |

> Les matériaux rares (Bout de Ficelle Cosmique, Larme de Gérard, Poussière de Narrateur) ne sont **jamais** vendus en boutique. Ils se méritent.

### 5.4 — Boutiques spéciales PNJ (débloquées par réputation/relation)

| PNJ | Seuil relation | Objets spéciaux |
|-----|---------------|-----------------|
| Elara (Forêt) | 50+ | Équipement elfique Rare (arcs, armures légères) à -20% prix |
| Thorin (Mines) | 50+ | Équipement nain Rare (haches, armures lourdes) à -20% prix |
| Magus (Tour) | 75+ | Orbes et bâtons Épique, parchemins d'enchantement |
| Le Syndicaliste (Cimetière) | 50+ | Équipement nécrotique Rare, consommables anti-mort-vivant |
| Le Dragon (Volcan) | 75+ | Équipement draconique Épique, écailles pour crafting |

### 5.5 — Boutique événementielle (boss mondial)

Pendant un boss mondial, une boutique temporaire apparaît avec des consommables spéciaux :

| Objet | Prix | Effet |
|-------|------|-------|
| Potion de Rage de Boss | 500 or | +20% ATQ contre le boss mondial (1 tentative) |
| Amulette Anti-Mécanique | 1000 or | Ignore 1 activation de la mécanique spéciale du boss (1 utilisation) |
| Baume de Groupe | 800 or | Soigne toute l'équipe de 30% PV avant la tentative |
| Parchemin d'Analyse | 300 or | Révèle les stats et faiblesses du boss |

---

## 6. Système d'enchantement

L'enchantement permet d'ajouter ou améliorer un effet spécial sur un objet existant.

### 6.1 — Règles

```
Conditions :
  - L'objet doit être Rare ou supérieur
  - Un objet peut avoir maximum 2 effets spéciaux
  - Si l'objet a déjà 2 effets, l'enchantement remplace le dernier
  - L'enchantement coûte de l'or + des matériaux

Processus :
  1. Choisir l'objet à enchanter
  2. Choisir l'enchantement souhaité (parmi ceux disponibles)
  3. Payer le coût
  4. L'enchantement est appliqué (pas de chance d'échec)
  
Disponibilité :
  - Enchantements de base : toujours disponibles chez Gérard
  - Enchantements avancés : débloqués par relation avec Magus (Tour)
  - Enchantements élémentaires : débloqués par matériaux de zone
```

### 6.2 — Liste des enchantements

**Enchantements de base (Gérard) :**

| Enchantement | Coût or | Matériaux | Effet |
|-------------|---------|-----------|-------|
| Aiguisage | 2000 | 5 Ferraille + 2 Essence Mineure | ATQ +8% |
| Renforcement | 2000 | 5 Ferraille + 2 Cuir | DEF +8% |
| Allègement | 2000 | 3 Cuir + 2 Gemme Brute | VIT +8% |
| Chance | 2500 | 3 Gemme Brute + 1 Essence Mineure | CHA +8% |
| Vitalité | 2500 | 5 Cuir + 2 Essence Mineure | PV +10% |
| Sagesse | 2500 | 3 Essence Mineure + 1 Cristal Brut | INT +8% |
| Solidité | 1500 | 8 Ferraille | Durabilité +50 |

**Enchantements avancés (Magus, relation 75+) :**

| Enchantement | Coût or | Matériaux | Effet |
|-------------|---------|-----------|-------|
| Vampirisme | 8000 | 3 Cristal Brut + 1 Essence Majeure | Soigne 5% des dégâts infligés |
| Précision | 6000 | 2 Cristal Brut + 2 Gemme Brute | Critique +8% |
| Esquive | 6000 | 2 Cristal Brut + 3 Cuir | Esquive +6% |
| Indestructible | 10000 | 5 Cristal Brut + 2 Essence Majeure | Durabilité infinie |
| Prospérité | 5000 | 2 Cristal Brut + 3 Gemme Brute | +10% or trouvé |

**Enchantements élémentaires (matériaux de zone) :**

| Enchantement | Coût or | Matériaux | Effet |
|-------------|---------|-----------|-------|
| Flamme | 4000 | 3 Cendre Volcanique + 1 Cristal Brut | Dégâts deviennent Feu + 10% dégâts |
| Givre | 4000 | 3 Glace Éternelle + 1 Cristal Brut | Dégâts deviennent Glace + 8% chance Ralenti |
| Foudre | 4000 | 3 Éclat de Foudre + 1 Cristal Brut | Dégâts deviennent Foudre + 5% chance Étourdi |
| Venin | 4000 | 3 Sève Toxique + 1 Cristal Brut | Dégâts deviennent Poison + Empoisonné 2 tours |
| Sacré | 5000 | 3 Essence d'Ombre + 1 Essence Majeure | Dégâts deviennent Sacré + 15% dégâts aux morts-vivants |

---

## 7. Matériaux — Système complet

### 7.1 — Matériaux génériques (toutes zones)

Obtenus par démontage d'objets et drops occasionnels.

| Matériau | Rareté | Obtention principale | Utilisation |
|----------|--------|---------------------|-------------|
| Ferraille | Commun | Démontage Commun/Peu commun, drop | Recettes de base, enchantements basiques |
| Cuir | Commun | Démontage bottes, drop monstres bêtes | Recettes d'armures, enchantements |
| Gemme Brute | Peu commun | Démontage accessoires, drop | Recettes d'accessoires, enchantements |
| Essence Mineure | Peu commun | Démontage Rare, drop zones magiques | Recettes intermédiaires, enchantements |
| Cristal Brut | Rare | Démontage Épique, drop rare | Recettes avancées, enchantements avancés |
| Essence Majeure | Rare | Démontage Légendaire, boss | Recettes haut niveau, enchantements puissants |
| Fragment Stellaire | Très rare | Démontage WTF, boss mondiaux | Recettes légendaires |
| Bout de Ficelle Cosmique | Ultra rare | Démontage WTF uniquement | Recettes WTF |
| Larme de Gérard | Spécial | 10% chance sur échec de fusion | Recettes secrètes |
| Poussière de Narrateur | Spécial | Événements spéciaux, quêtes WTF | Recettes cosmétiques et secrètes |

### 7.2 — Matériaux de zone (thématiques)

Chaque zone drop ses propres matériaux uniques utilisés pour les recettes de zone et les enchantements élémentaires.

**Zone 1 — Prairie des Débutants**

| Matériau | Drop chance/combat | Source | Utilisation |
|----------|--------------------|--------|-------------|
| Herbe Folle | 20% | Monstres, idle | Potions de soin basiques |
| Corne de Bête | 10% | Loups, Taureau | Recette Amulette du Débutant |
| Laine Enchantée | 5% | Moutons (idle event) | Recette Cape du Berger |

**Zone 2 — Forêt des Elfes Vexés**

| Matériau | Drop chance/combat | Source | Utilisation |
|----------|--------------------|--------|-------------|
| Bois Elfique | 15% | Treants, Chêne Furieux | Recettes arcs elfiques, armures en bois |
| Sève Toxique | 12% | Araignées, Champignons | Enchantement Venin, potions de poison |
| Plume de Fée | 5% | Fées Maléfiques | Recette accessoire de VIT |
| Fil d'Araignée | 8% | Araignées | Recette armure légère Rare |

**Zone 3 — Mines du Nain Ivre**

| Matériau | Drop chance/combat | Source | Utilisation |
|----------|--------------------|--------|-------------|
| Minerai Brut | 18% | Golems, idle mining | Recettes armes et armures lourdes |
| Pierre Lumineuse | 8% | Chauves-souris, coffres | Recette Torche Éternelle améliorée |
| Bière Enchantée | 5% | Nains Fantômes, Thorin | Recette Potion de Thorin |
| Éclat de Lave | 10% | Salamandres | Enchantement Flamme, recettes feu |

**Zone 4 — Marais de la Bureaucratie**

| Matériau | Drop chance/combat | Source | Utilisation |
|----------|--------------------|--------|-------------|
| Papier Officiel | 15% | Bureaucrates, Archivistes | Recettes accessoires CHA |
| Encre Toxique | 10% | Sangsues, Crocodiles | Enchantement Venin avancé |
| Tampon Usé | 8% | Tamponneurs, Chef de Service | Recette arme "Tampon" |
| Vase Magique | 5% | Grenouilles, idle | Recette bottes de marais |

**Zone 5 — Tour du Mage Distrait**

| Matériau | Drop chance/combat | Source | Utilisation |
|----------|--------------------|--------|-------------|
| Éclat de Foudre | 12% | Élémentaires, Horloges | Enchantement Foudre |
| Page Enchantée | 10% | Livres Mordeurs | Recette parchemins puissants |
| Rouage Magique | 8% | Armures Animées, Horloges | Recette accessoire mécanique |
| Poussière Arcanique | 15% | Apprentis, Familiar | Recettes magiques, potions INT |

**Zone 6 — Cimetière Syndiqué**

| Matériau | Drop chance/combat | Source | Utilisation |
|----------|--------------------|--------|-------------|
| Essence d'Ombre | 12% | Tous les morts-vivants | Enchantement Sacré |
| Os Poli | 15% | Squelettes | Recettes armes et armures nécrotiques |
| Ectoplasme | 8% | Fantômes | Recette potions spectrales |
| Bandage Maudit | 10% | Zombies | Recette armure Rare nécrotique |

**Zone 7 — Volcan du Dragon Retraité**

| Matériau | Drop chance/combat | Source | Utilisation |
|----------|--------------------|--------|-------------|
| Cendre Volcanique | 15% | Tous les monstres de feu | Enchantement Flamme |
| Écaille de Dragon | 3% | Dragon (boss), Wyrms | Recettes draconiques Épique |
| Obsidienne | 10% | Golems, idle mining | Recettes armes Rare/Épique |
| Plume de Phénix | 5% | Phénix Mineur | Recette résurrection / regen |

**Zone 8 — Capitale des Incompétents**

| Matériau | Drop chance/combat | Source | Utilisation |
|----------|--------------------|--------|-------------|
| Glace Éternelle | 12% | Phase 1 du Maire (Glace) | Enchantement Givre |
| Insigne de Guilde | 8% | Gardes, Maître de Guilde | Recettes accessoires de prestige |
| Acier de Ville | 15% | Golems, Gardes | Recettes armes et armures haut niveau |
| Poudre d'Ombre | 10% | Assassins | Recettes équipement furtif |

### 7.3 — Matériaux rares cross-zone

Des matériaux qui peuvent drop dans n'importe quelle zone avec une très faible chance. Utilisés pour les recettes les plus puissantes.

| Matériau | Drop chance globale | Utilisation |
|----------|-------------------|-------------|
| Étoile Tombée | 1% par combat (toutes zones niv. 30+) | Recettes Légendaire, composant universel |
| Sang de Héros | 2% quand un héros tombe K.O. en combat | Recettes Épique spéciales |
| Larme du Monde | 1% par boss de zone vaincu | Recette WTF cross-zone |
| Fil du Destin | 3% en quête WTF uniquement | Recette accessoire Légendaire |

---

## 8. Recettes complètes

### 8.1 — Recettes de consommables

| Recette | Matériaux | Coût or | Résultat | Découverte |
|---------|-----------|---------|----------|-----------|
| Potion de Soin | 3 Herbe Folle | 50 | Soigne 30% PV max | Dès le début |
| Potion de Soin+ | 2 Herbe Folle + 1 Essence Mineure | 150 | Soigne 60% PV max | Dès le début |
| Potion de Soin Ultime | 2 Essence Mineure + 1 Plume de Phénix | 500 | Soigne 100% PV max | Zone 7 |
| Bière de Thorin | 3 Bière Enchantée + 2 Minerai Brut | 300 | +20% ATQ, +20% PV, -10% INT (30 combats) | Thorin relation 100 |
| Élixir Élémentaire (Feu) | 3 Cendre Volcanique + 1 Éclat de Lave | 400 | +20% dégâts Feu (10 combats) | Zone 7 |
| Élixir Élémentaire (Glace) | 3 Glace Éternelle + 1 Poussière Arcanique | 400 | +20% dégâts Glace (10 combats) | Zone 8 |
| Élixir Élémentaire (Foudre) | 3 Éclat de Foudre + 1 Rouage Magique | 400 | +20% dégâts Foudre (10 combats) | Zone 5 |
| Antidote Puissant | 2 Sève Toxique + 1 Herbe Folle | 100 | Retire Empoisonné + immunité Poison 5 combats | Zone 2 |
| Parchemin de Fuite | 5 Ferraille | 30 | Fuite automatique garantie | Dès le début |
| Pierre d'Aiguisage | 4 Ferraille + 1 Gemme Brute | 80 | +10% ATQ pour 5 combats | Dès le début |
| Kit de Réparation | 10 Ferraille | 100 | Répare 30 durabilité | Dès le début |
| Parchemin de Révélation | 3 Page Enchantée + 1 Poussière Arcanique | 200 | Révèle les stats du Mythomane pendant 24h | Zone 5 |
| Encens Anti-Allergie | 2 Herbe Folle + 2 Poussière Arcanique | 300 | Supprime l'allergie magique pour 20 combats | Zone 5 |

### 8.2 — Recettes d'équipement par zone

**Prairie (Zone 1) :**

| Recette | Matériaux | Coût or | Résultat |
|---------|-----------|---------|----------|
| Amulette du Débutant | 5 Ferraille + 3 Cuir | 200 | Accessoire Peu commun : +5% XP |
| Cape du Berger | 3 Laine Enchantée + 5 Cuir | 400 | Armure Peu commun : DEF + résistance froid |
| Gourdin de Corne | 4 Corne de Bête + 3 Ferraille | 300 | Arme Peu commun : ATQ + 5% chance Étourdi |

**Forêt (Zone 2) :**

| Recette | Matériaux | Coût or | Résultat |
|---------|-----------|---------|----------|
| Arc des Elfes Vexés | 5 Bois Elfique + 3 Fil d'Araignée | 800 | Arme Rare : ATQ + 10% critique en forêt |
| Armure de Soie | 5 Fil d'Araignée + 3 Cuir | 1000 | Armure Rare : DEF + VIT, légère |
| Diadème de Fée | 3 Plume de Fée + 2 Gemme Brute | 1200 | Casque Rare : INT + CHA |

**Mines (Zone 3) :**

| Recette | Matériaux | Coût or | Résultat |
|---------|-----------|---------|----------|
| Hache du Mineur | 5 Minerai Brut + 3 Ferraille + 1 Éclat de Lave | 1500 | Arme Rare : ATQ élevée + bonus mine |
| Casque du Nain Blindé | 8 Minerai Brut + 2 Pierre Lumineuse | 1800 | Casque Rare : DEF + résistance Étourdi |
| Lanterne de Mine | 3 Pierre Lumineuse + 5 Ferraille | 1000 | Accessoire Rare : +15% loot en souterrain |

**Marais (Zone 4) :**

| Recette | Matériaux | Coût or | Résultat |
|---------|-----------|---------|----------|
| Formulaire en Triple | 8 Papier Officiel + 3 Encre Toxique | 2500 | Accessoire Épique : "Paperasserie" 10% /combat |
| Bottes du Marais | 5 Vase Magique + 3 Cuir | 2000 | Bottes Rare : VIT + immunité Ralenti |
| Stylo-Dague | 5 Encre Toxique + 3 Tampon Usé + 1 Cristal Brut | 3000 | Arme Épique : ATQ + INT, dégâts Poison |

**Tour (Zone 5) :**

| Recette | Matériaux | Coût or | Résultat |
|---------|-----------|---------|----------|
| Orbe Instable | 5 Poussière Arcanique + 3 Éclat de Foudre + 1 Cristal Brut | 4000 | Arme Épique : INT, variance élargie |
| Robe du Temps | 5 Rouage Magique + 3 Page Enchantée | 3500 | Armure Épique : DEF + INT, 5% chance skip ennemi |
| Montre de Poche Folle | 3 Rouage Magique + 2 Éclat de Foudre + 1 Gemme Brute | 3000 | Accessoire Rare : VIT +15%, initiative toujours calculée deux fois (garde le meilleur) |

**Cimetière (Zone 6) :**

| Recette | Matériaux | Coût or | Résultat |
|---------|-----------|---------|----------|
| Faux du Fossoyeur | 8 Os Poli + 3 Essence d'Ombre + 1 Cristal Brut | 5000 | Arme Épique : ATQ, dégâts Sacré, +25% vs morts-vivants |
| Cape d'Ectoplasme | 5 Ectoplasme + 3 Bandage Maudit | 4000 | Armure Rare : DEF, 10% esquive, résistance Ombre |
| Crâne Ricanant | 5 Os Poli + 3 Ectoplasme + 1 Essence Majeure | 6000 | Accessoire Épique : INT + CHA, "Rire Macabre" : 8% chance de Terrifié sur l'ennemi |

**Volcan (Zone 7) :**

| Recette | Matériaux | Coût or | Résultat |
|---------|-----------|---------|----------|
| Lame de Magma | 8 Obsidienne + 3 Cendre Volcanique + 2 Cristal Brut | 8000 | Arme Épique : ATQ élevée, dégâts Feu, "En feu" 15% |
| Plastron Draconique | 3 Écaille de Dragon + 5 Obsidienne + 1 Essence Majeure | 12000 | Armure Épique : DEF très élevée, résistance Feu +30% |
| Anneau du Phénix | 3 Plume de Phénix + 2 Cendre Volcanique + 1 Fragment Stellaire | 15000 | Accessoire Légendaire : 1×/combat, si K.O. revient à 30% PV |

**Capitale (Zone 8) :**

| Recette | Matériaux | Coût or | Résultat |
|---------|-----------|---------|----------|
| Épée du Champion | 8 Acier de Ville + 3 Insigne de Guilde + 2 Essence Majeure | 10000 | Arme Épique : ATQ très élevée, +10% toutes stats en Capitale |
| Armure de Prestige | 5 Acier de Ville + 3 Insigne de Guilde + 5 Cuir | 8000 | Armure Épique : DEF + CHA, -10% prix boutiques |
| Dague de l'Ombre | 5 Poudre d'Ombre + 3 Acier de Ville + 1 Essence Majeure | 9000 | Arme Épique : ATQ, dégâts Ombre, "Embuscade" : premier coup toujours critique |

### 8.3 — Recettes cross-zone (matériaux rares)

| Recette | Matériaux | Coût or | Résultat |
|---------|-----------|---------|----------|
| Amulette du Destin | 3 Étoile Tombée + 2 Fil du Destin + 1 Fragment Stellaire | 20000 | Accessoire Légendaire : toutes stats +10%, +10% loot, +10% XP |
| Lame du Héros Déchu | 5 Sang de Héros + 3 Essence Majeure + 2 Obsidienne | 18000 | Arme Légendaire : ATQ massive, +15% dégâts quand un allié est K.O. |
| Couronne des Incompétents | 3 Larme du Monde + 3 Poussière de Narrateur + 1 Bout de Ficelle Cosmique | 25000 | Casque Légendaire : toutes stats +8%, les traits négatifs se déclenchent 5% plus souvent MAIS les effets de la Branche du Défaut sont doublés |
| Tablier de Gérard | 5 Larme de Gérard + 10 Ferraille | 5000 | Armure Légendaire : DEF, immunité aux échecs de fusion |
| Micro du Narrateur | 3 Poussière de Narrateur + 1 Fragment Stellaire | 8000 | Accessoire Légendaire : +20% XP, commentaires du Narrateur améliorés |

---

## 9. Flux économique — Vue d'ensemble

```
                        ENTRÉES D'OR
                            │
    ┌───────────┬───────────┼───────────┬───────────┐
    │           │           │           │           │
  Combats    Quêtes    Vente objets  Boss     Idle events
  (60%)      (20%)      (10%)      (5%)       (5%)
    │           │           │           │           │
    └───────────┴───────────┼───────────┴───────────┘
                            │
                        RÉSERVE D'OR
                            │
    ┌───────────┬───────────┼───────────┬───────────┐
    │           │           │           │           │
 Boutiques  Crafting   Réparation  Services  Enchantement
  (30%)      (25%)      (20%)      (15%)      (10%)
    │           │           │           │           │
    └───────────┴───────────┼───────────┴───────────┘
                            │
                     DÉPENSES D'OR


                      ENTRÉES MATÉRIAUX
                            │
    ┌───────────┬───────────┼───────────┐
    │           │           │           │
  Drops     Démontage    Boss     Idle events
  (50%)      (30%)      (10%)      (10%)
    │           │           │           │
    └───────────┴───────────┼───────────┘
                            │
                     STOCK MATÉRIAUX
                            │
    ┌───────────┬───────────┼───────────┐
    │           │           │           │
  Recettes  Enchantements  Consommables
  (50%)      (30%)          (20%)
    │           │           │
    └───────────┴───────────┘
                            │
                   DÉPENSES MATÉRIAUX
```

---

## 10. Anti-inflation — Mécanismes de contrôle

| Mécanisme | Effet |
|-----------|-------|
| Réparation constante | Drain régulier proportionnel au niveau |
| Prix boutique ×3 | Acheter coûte beaucoup plus que vendre |
| Fusion destructive | 3 objets détruits pour 1 (avec chance d'échec) |
| Reset talents croissant | Chaque reset coûte 150% du précédent |
| Cadeaux PNJ | Gold sink quotidien optionnel mais tentant |
| Enchantement | Gros investissement ponctuel |
| Consommables | Drain constant si le joueur les utilise |
| Gourmand (trait) | Un héros Gourmand coûte plus cher à entretenir |
| Boutique événementielle | Consommables temporaires chers pendant les boss mondiaux |
| Inventaire plein → auto-vente | Le joueur perd de la valeur s'il ne gère pas son inventaire |

---

## 11. Résumé des prix clés (niveau 30)

| Action | Coût approximatif | Temps de farm |
|--------|-------------------|---------------|
| Potion de Soin basique | 900 or | ~20 min |
| Objet Rare en boutique | 5400 or | ~2h |
| Objet Épique en boutique | 13500 or | ~5h |
| Fusion de 3 Rares | 6000 or + objets | ~2h (or seul) |
| Enchantement basique | 2000 or + matériaux | ~45 min |
| Enchantement avancé | 8000 or + matériaux | ~3h |
| Recette Épique de zone | 3000-8000 or + matériaux | ~1-3h |
| Recette Légendaire cross-zone | 18000-25000 or + matériaux rares | ~8-10h |
| Reset talents (1er) | 500 or | ~10 min |
| Cadeau PNJ | 6000 or | ~2h |
| Réparation complète (5 objets) | ~5000 or | ~2h |
