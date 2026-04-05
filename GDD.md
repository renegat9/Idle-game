# 🏰 Game Design Document — *Le Donjon des Incompétents*
### Idle RPG Fantasy Humoristique

**Version :** 1.0  
**Date :** 30 mars 2026  
**Genre :** Idle Game + Choix Actifs  
**Thème :** Fantasy humoristique (inspiré de Kaamelott, Donjon de Naheulbeuk, Munchkin)  
**Modèle :** Gratuit (hobby / portfolio)

---

## 1. Vision du jeu

Le joueur incarne un aventurier incompétent qui recrute progressivement une équipe de héros tout aussi médiocres pour explorer des donjons, accomplir des quêtes absurdes et crafter des objets ridicules — le tout commenté par un narrateur sarcastique généré par IA.

**Pitch en une phrase :** *"Un idle game où tes héros sont nuls, ton loot est absurde, et le narrateur te déteste."*

**Pilliers de design :**
- L'humour est omniprésent — chaque système doit faire sourire
- La progression est satisfaisante malgré (grâce à) l'incompétence des héros
- L'IA génère du contenu unique à chaque partie (narration, objets, images, musique)
- Le monde s'étend continuellement pour éviter le plafonnement

---

## 2. Stack technique

| Composant | Technologie | Justification |
|-----------|-------------|---------------|
| Frontend | React + TypeScript + Vite | UI réactive, build statique pour cPanel |
| Backend | PHP 8.2+ / Laravel | Support natif cPanel, ORM, queues, cron |
| Base de données | MariaDB | Requis par le projet |
| Cache | Laravel File/DB Cache | Pas de Redis sur cPanel |
| Auth | Laravel Sanctum (tokens) | Session unique, SPA-friendly |
| IA — Texte | API Gemini (text generation) | Narration, noms d'objets, dialogues |
| IA — Images | API Gemini (Imagen) | Illustrations de loot, monstres, héros |
| IA — Musique | API Gemini (MusicFX) | Ambiances de taverne dynamiques |
| Hébergement | cPanel (VPS petit budget) | Apache, PHP natif, cron natif |
| Communication | API REST + polling AJAX | Pas de WebSockets sur cPanel |

---

## 3. Boucle de gameplay principale

```
┌─────────────────────────────────────────────────────────────┐
│                    BOUCLE PRINCIPALE                        │
│                                                             │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐              │
│  │ EXPLORER │───>│ COMBATTRE│───>│ RÉCOLTER │              │
│  │ (idle)   │    │ (auto)   │    │ (loot)   │              │
│  └──────────┘    └──────────┘    └──────────┘              │
│       │                               │                     │
│       │         ┌──────────┐          │                     │
│       │         │ NARRATEUR│ (commente tout, tout le temps) │
│       │         └──────────┘          │                     │
│       │                               ▼                     │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐              │
│  │ NOUVELLE │<───│ CRAFTER  │<───│ ÉQUIPER  │              │
│  │   ZONE   │    │ (actif)  │    │ (choix)  │              │
│  └──────────┘    └──────────┘    └──────────┘              │
│       │                                                     │
│       ▼                                                     │
│  ┌──────────────────┐    ┌───────────────────┐             │
│  │ QUÊTES ACTIVES   │    │ BOSS MONDIAUX     │             │
│  │ (embranchements) │    │ (événements)      │             │
│  └──────────────────┘    └───────────────────┘             │
└─────────────────────────────────────────────────────────────┘
```

**Idle (passif) :** Les héros explorent et combattent automatiquement. Le joueur collecte les récompenses.

**Actif :** Le joueur intervient pour les quêtes à embranchements, le crafting, la gestion d'équipe, et les boss mondiaux.

**Offline :** À la reconnexion, le serveur calcule la progression écoulée (avec un cap pour éviter l'abus).

---

## 4. Systèmes de jeu détaillés

### 4.1 — Système de héros

#### Création et recrutement

Le joueur commence avec **un seul héros** créé à l'inscription. Il peut ensuite recruter jusqu'à **4 héros supplémentaires** (équipe max de 5) via la Taverne de Recrutement.

Chaque héros est défini par trois composantes :

**RACE** (détermine les stats de base et un bonus passif)

| Race | PV | ATQ | DEF | Bonus passif |
|------|-----|-----|-----|--------------|
| Humain | Moyen | Moyen | Moyen | +10% XP (seule qualité) |
| Elfe | Bas | Haut | Bas | +15% précision (quand il daigne se battre) |
| Nain | Haut | Haut | Haut | +20% loot en mine (motivation par la bière) |
| Gobelin | Bas | Moyen | Bas | +25% or trouvé (chapardage instinctif) |
| Orc | Très haut | Très haut | Bas | +10% dégâts critiques (manque de subtilité) |
| Demi-troll | Très haut | Moyen | Très haut | Régénération passive (cerveau en option) |

**CLASSE** (détermine le rôle, les compétences et le style de combat)

| Classe | Rôle | Compétence clé |
|--------|------|----------------|
| Guerrier | Tank / DPS | Charge Héroïque (fonce sans réfléchir) |
| Mage | DPS magique | Boule de feu (vise pas toujours bien) |
| Voleur | DPS / Utilitaire | Coup en traître (bonus si attaque par derrière) |
| Ranger | DPS distance | Tir "précis" (dépend du vent, de l'humeur, de la lune) |
| Prêtre | Soigneur | Prière de soin (résultats variables selon la foi du jour) |
| Barde | Support / Buff | Chanson inspirante (ou pas, selon le public) |
| Barbare | DPS pur | Rage (casse tout, y compris le décor) |
| Nécromancien | Invocateur | Invoquer squelette (qui obéit... parfois) |

**TRAIT NÉGATIF** (attribué aléatoirement, impacte le gameplay — chaque héros en a un)

| Trait | Effet mécanique | Flavor text |
|-------|-----------------|-------------|
| Couard | 15% de chance de fuir un combat | "La fuite, c'est une stratégie." |
| Narcoleptique | 10% de chance de s'endormir pendant un combat (skip un tour) | "Zzz... Quoi ? On est attaqués ?" |
| Kleptomane | Vole parfois le loot des alliés | "C'était dans ma poche depuis le début." |
| Pyromane | 20% de chance de mettre le feu au décor (dégâts de zone... alliés inclus) | "Le feu, ça résout tout." |
| Allergique à la magie | Malus en zone magique, éternue et révèle la position | "ATCHOUM — ah, le boss nous a vus." |
| Philosophe | S'arrête en plein combat pour réfléchir au sens de la vie (skip un tour) | "Mais au fond, pourquoi combattre ?" |
| Gourmand | Consomme les potions de soin automatiquement même à PV max | "C'était pas du jus de pomme ?" |
| Superstitieux | Refuse d'entrer dans certains donjons selon le jour | "Pas un mardi ! Jamais un mardi !" |
| Mythomane | Ses stats affichées sont fausses (±20% aléatoire) | "J'ai déjà tué un dragon. Enfin, un gros lézard." |
| Pacifiste | Refuse d'attaquer certains ennemis "trop mignons" | "Non mais regarde sa petite tête !" |

> **Mécanique clé :** Les traits négatifs ne sont pas que des malus — certaines combinaisons créent des synergies cachées. Un Voleur Kleptomane vole PLUS de loot. Un Barbare Pyromane fait des dégâts de zone massifs. Un Barde Narcoleptique a une berceuse surpuissante. Découvrir ces synergies fait partie du jeu.

#### Progression des héros

- **Niveaux 1–100** par héros (XP gagnée en idle et en quêtes)
- Chaque niveau débloque des points de compétence
- Arbre de talents en 3 branches par classe (une branche "embrasse le défaut" pour les synergies)
- **Pas de reset de niveaux** — le monde s'étend pour suivre la progression

#### Taverne de recrutement

- 3 héros aléatoires proposés (race + classe + trait), renouvelés toutes les 4 heures
- Coût en or croissant par slot débloqué
- Possibilité de "renvoyer" un héros (slot libéré, héros perdu)
- Événements rares : héros légendaires avec des traits négatifs spéciaux encore plus absurdes

---

### 4.2 — Le Narrateur Sarcastique

Un PNJ omniscient et omniprésent qui commente TOUT ce que fait le joueur, dans un style mêlant la voix off du Naheulbeuk et les répliques de Kaamelott.

#### Fonctionnement technique

- **Templates contextuels :** Une bibliothèque de ~50 templates par type d'événement (combat, loot, quête, recrutement, mort, etc.)
- **Gemini enrichit les templates :** Le serveur envoie le contexte (événement + héros + situation) à l'API Gemini qui génère une variante unique à partir du template
- **Cache agressif :** Les commentaires générés sont stockés en base et réutilisés pour des situations similaires, limitant les appels API
- **File d'attente :** Les générations IA passent par les Laravel Jobs (cron) pour ne pas bloquer le gameplay

#### Exemples par contexte

**Combat gagné :**
> "Victoire ! Enfin, 'victoire'... Vous avez vaincu un rat. Niveau 1. Qui dormait. Bravo."

**Mort d'un héros :**
> "Gruntak le Barbare Pyromane est mort. Il s'est mis le feu. Encore. La prochaine fois, essayez l'eau."

**Loot trouvé :**
> "Vous avez trouvé une Épée en Mousse du Destin +3. C'est... c'est une vraie arme, ça ? Ah, apparemment oui."

**Recrutement :**
> "Vous recrutez un Elfe Ranger Narcoleptique. Ses flèches sont précises. Quand il est éveillé. Soit environ 20% du temps."

**Échec de quête :**
> "La quête est un échec. Non, vraiment, vous avez trouvé le moyen d'échouer une quête FedEx. Chapeau."

#### Paramètres joueur

- Fréquence des commentaires : Bavard / Normal / Discret / Muet
- Le narrateur "s'adapte" au joueur : plus le joueur est bon, plus il est sarcastique. Plus le joueur galère, plus il est condescendant.

---

### 4.3 — Exploration et Donjons

#### Le monde

Le monde est divisé en **zones** débloquées progressivement :

| Zone | Niveau requis | Thème | Particularité |
|------|---------------|-------|---------------|
| 1. La Prairie des Débutants | 1 | Campagne générique | Tutoriel narratif (le narrateur se moque) |
| 2. La Forêt des Elfes Vexés | 5 | Forêt enchantée | Les PNJ elfes sont hautains et inutiles |
| 3. Les Mines du Nain Ivre | 12 | Souterrain | Pièges absurdes, bonus bière |
| 4. Le Marais de la Bureaucratie | 20 | Marécage administratif | Ennemis = formulaires, tampons, files d'attente |
| 5. La Tour du Mage Distrait | 30 | Tour magique | Gravité inversée, sorts aléatoires |
| 6. Le Cimetière Syndiqué | 42 | Mort-vivants | Les squelettes ont des horaires de travail |
| 7. Le Volcan du Dragon Retraité | 55 | Volcanique | Le boss est un dragon pacifiste |
| 8. La Capitale des Incompétents | 70 | Ville | Hub avancé, quêtes politiques |
| 9+ | 85+ | Générées par IA | Zones thématiques uniques par serveur |

> Les zones 9+ sont générées dynamiquement par Gemini (nom, thème, ennemis, loot) pour assurer un contenu infini et unique.

#### Exploration idle

- Le joueur assigne son équipe à une zone
- Les héros explorent automatiquement : rencontres toutes les X secondes
- Chaque rencontre = combat auto-résolu basé sur les stats
- Loot et XP collectés automatiquement
- Le joueur peut récupérer les récompenses à la reconnexion

#### Donjons spéciaux

- **Disponibilité :** Un nouveau donjon apparaît toutes les 8 heures (timer par joueur)
- **Structure :** 5 à 15 salles avec des rencontres, des pièges, des coffres et un boss
- **Génération :** Layout procédural + Gemini pour la narration de chaque salle
- **Difficulté :** Basée sur le niveau moyen de l'équipe + multiplicateur choisi par le joueur (plus risqué = meilleur loot)

---

### 4.4 — Système de combat

Le combat est **automatique** mais le joueur peut voir le déroulement en mode "replay".

#### Résolution

```
Pour chaque tour :
  1. Ordre d'initiative (basé sur vitesse + aléatoire)
  2. Vérification des traits négatifs (Couard fuit ? Narcoleptique dort ?)
  3. Chaque héros/ennemi agit :
     - Attaque de base OU compétence (si disponible)
     - Calcul : Dégâts = ATQ × (1 - DEF_cible/200) × modificateurs
     - Chance de critique : 5% + bonus
  4. Vérification de mort / fin de combat
  5. Le Narrateur commente les moments clés
```

#### Combat en mode actif (quêtes)

Pendant les quêtes à embranchements, le joueur peut intervenir :
- Choisir la cible prioritaire
- Activer un objet consommable
- Ordonner une retraite (avec moquerie du Narrateur)

---

### 4.5 — Loot Absurde (Système d'objets)

#### Rareté

| Rareté | Couleur | Chance de drop | Nombre de stats |
|--------|---------|----------------|-----------------|
| Commun | Gris | 50% | 1 |
| Peu commun | Vert | 25% | 2 |
| Rare | Bleu | 14% | 3 |
| Épique | Violet | 7% | 4 |
| Légendaire | Orange | 3% | 5 |
| Wtf | Rose clignotant | 1% | 5 + effet unique absurde |

> **Règle fondamentale :** Toutes les valeurs numériques du jeu sont des **entiers**. Pas de décimales nulle part — stats, dégâts, pourcentages, or, PV. Ça simplifie le code, la DB et l'affichage.

#### Génération par IA

Chaque objet Rare+ est généré dynamiquement :

**Entrée Gemini (texte) :**
```json
{
  "slot": "arme",
  "rarity": "épique",
  "zone": "Le Marais de la Bureaucratie",
  "style": "fantasy humoristique, objet absurde et décalé"
}
```

**Sortie attendue :**
```json
{
  "name": "Tampon Officiel de Destruction +4",
  "description": "Un tampon administratif si puissant qu'il valide l'existence même de vos ennemis. Comme 'annulée'.",
  "stats": { "atq": 45, "vitesse": -10 },
  "special": "10% de chance d'infliger 'Paperasserie' : l'ennemi perd un tour à remplir un formulaire."
}
```

**Image Gemini (Imagen) :**
- Générée à partir du nom + description
- Prompt style cohérent : "cartoon fantasy item, humorous, colorful, RPG style, white background"
- Stockée en `storage/loot/` avec l'ID de l'objet, jamais regénérée

#### Slots d'équipement

Chaque héros a 6 slots : Arme, Armure, Casque, Bottes, Accessoire, Truc Bizarre (slot spécial pour les objets WTF).

#### Objets fixes (base de données)

Les objets Communs et Peu Communs viennent d'une table pré-remplie (pas de génération IA) pour économiser les appels API. Seuls les Rare+ déclenchent Gemini.

---

### 4.6 — Crafting / Forge d'objets

#### La Forge de Gérard

Gérard est un forgeron incompétent mais enthousiaste. Le crafting est **actif** — le joueur choisit quoi combiner.

#### Mécanique

- **Fusion :** Combiner 3 objets de même rareté → 1 objet de rareté supérieure (résultat aléatoire, influencé par les ingrédients)
- **Démontage :** Détruire un objet → récupérer des matériaux (Ferraille, Essence Magique, Bout de Ficelle, etc.)
- **Recettes spéciales :** Certaines combinaisons spécifiques donnent des objets uniques nommés

#### Crafting IA

Quand le joueur fusionne des objets Rare+, Gemini génère le résultat :

```json
{
  "input_items": ["Épée Rouillée +2", "Baguette de Pain Magique", "Chaussette du Destin"],
  "target_rarity": "épique",
  "style": "fantasy humoristique"
}
```

> Résultat possible : "L'Épée-Tartine du Destin — Une lame qui sent le pain frais et qui tranche la réalité (et le beurre)."

#### Échecs de craft

- 15% de chance d'échec (l'objet explose, Gérard s'excuse, le Narrateur commente)
- Les échecs donnent parfois des résidus utiles ou des objets "ratés" amusants
- Gérard a ses propres répliques générées par Gemini selon le résultat

---

### 4.7 — Quêtes à embranchements absurdes

#### Structure

Chaque quête est une mini-aventure narrative avec **3 à 7 étapes**, chacune présentant un **choix** au joueur.

#### Types de quêtes

- **Quêtes de zone :** Liées à la zone en cours, débloquent la progression
- **Quêtes quotidiennes :** 3 par jour, courtes (3 étapes), récompenses modérées
- **Quêtes événementielles :** Liées aux boss mondiaux, collaboratives
- **Quêtes WTF :** Rares, très longues, très absurdes, très rémunératrices

#### Génération

**Quêtes de zone** — Pré-écrites (base de données) avec variantes. ~10 par zone, jouées dans l'ordre.

**Quêtes quotidiennes** — Générées par Gemini à partir d'un pool de templates :

```json
{
  "zone": "Forêt des Elfes Vexés",
  "team": ["Nain Guerrier Gourmand", "Elfe Mage Philosophe"],
  "difficulty": "normal",
  "template": "escort_quest"
}
```

#### Exemple d'embranchement

```
QUÊTE : "Le Pont du Troll Syndicaliste"

Étape 1 : Un troll bloque le pont. Il veut voir votre carte de membre du syndicat.
  → [A] Montrer une fausse carte → Étape 2A
  → [B] Négocier une cotisation → Étape 2B  
  → [C] Attaquer le troll → Étape 2C

Étape 2A : Le troll examine la carte. "C'est un menu de kebab."
  → [A] Prétendre que c'est un kebab syndical → Étape 3A (test charisme)
  → [B] Fuir → Étape 3B (le troll vous poursuit)

Étape 2B : Le troll demande 500 pièces d'or et un formulaire en triple exemplaire.
  → [A] Payer → Passe le pont, moins riche mais vivant
  → [B] Remplir le formulaire → Test de patience, Étape 3C
  ...
```

#### Résolution des choix

- Certains choix sont des **tests de stats** (force, charisme, intelligence) avec un résultat probabiliste
- Les traits négatifs influencent : un Philosophe a +30% au test de charisme mais -50% en patience
- Le Narrateur commente chaque choix (surtout les mauvais)
- Il n'y a pas de "mauvais" choix — chaque chemin mène quelque part d'intéressant (mais pas toujours de rentable)

---

### 4.8 — Taverne Musicale IA

#### Concept

La Taverne est un hub social du jeu où un barde (PNJ) joue de la musique dynamique générée par l'IA Gemini (MusicFX).

#### Fonctionnement

- La Taverne est accessible depuis le menu principal
- Le barde joue une ambiance par défaut (musique de taverne médiévale joyeuse)
- Le joueur peut **commander un morceau** en choisissant un style :

| Style | Description du prompt MusicFX |
|-------|-------------------------------|
| Victoire épique | "epic medieval victory fanfare, orchestral, triumphant" |
| Défaite lamentable | "sad medieval tavern song, minor key, solo lute, pathetic" |
| Exploration mystérieuse | "mysterious fantasy exploration, ambient, flute and harp" |
| Combat héroïque | "intense medieval battle music, drums and brass" |
| Repos au coin du feu | "calm medieval fireplace, gentle acoustic guitar, peaceful" |
| La complainte du Nain | "drunken dwarf drinking song, rowdy tavern choir, funny" |

- Les morceaux générés sont **cachés** : une fois générés, stockés en `storage/music/` et réutilisés
- Le pool de musiques grandit naturellement avec l'utilisation
- Joue en fond pendant que le joueur navigue dans le jeu (bascule automatique selon le contexte si activé)

#### Ambiance dynamique (optionnel, phase 2)

- La musique change automatiquement selon le contexte :
  - En exploration idle → Exploration mystérieuse
  - Combat de boss → Combat héroïque
  - Mort de l'équipe → Défaite lamentable
  - Retour en ville → Repos au coin du feu

---

### 4.9 — Événements mondiaux (Boss de serveur)

#### Concept

Des boss surpuissants apparaissent régulièrement et nécessitent la participation de **tous les joueurs du serveur** pour être vaincus.

#### Fonctionnement

- **Apparition :** Un boss mondial apparaît tous les 3 jours (timer serveur)
- **Annonce :** Le Narrateur prévient tout le monde avec une annonce dramatique (et moqueuse)
- **Participation :** Chaque joueur peut envoyer son équipe attaquer le boss (1 attaque toutes les 2 heures)
- **PV partagés :** Le boss a un pool de PV massif que tous les joueurs grignotent ensemble
- **Contribution :** Le serveur track les dégâts de chaque joueur
- **Récompenses :** Basées sur le % de contribution — plus tu fais de dégâts, meilleur le loot

#### Types de boss

| Boss | Mécanisme spécial |
|------|-------------------|
| Le Dragon Retraité | Fait des pauses café, moment de vulnérabilité |
| Le Kraken Comptable | Change les stats des joueurs en les "auditant" |
| L'Ancien Bureaucrate | Immunité sauf si on a le bon formulaire |
| Le Géant Narcoleptique | S'endort aléatoirement (DPS window) |
| La Hydre Syndicaliste | Chaque tête coupée fait grève et repousse en 2 |

#### Boss générés par IA (phase 2)

Gemini génère des boss uniques avec nom, mécaniques, description et illustration pour garder le contenu frais.

#### Récompenses

- **Tous les participants :** Médaille de participation (le Narrateur se moque) + coffre Commun
- **Top 50% :** Coffre Rare garanti
- **Top 10% :** **Objet unique thématique** lié au boss, généré par Gemini (nom, stats, illustration en rapport direct avec le boss vaincu). Ex : vaincre le Kraken Comptable → "Tentacule-Stylo du Kraken — Fouette les ennemis avec la puissance de la comptabilité analytique."
- **MVP (1er) :** Titre exclusif affiché sur le profil ("Pourfendeur du Kraken Comptable")

---

## 5. Économie du jeu

### Ressources

| Ressource | Obtention | Utilisation |
|-----------|-----------|-------------|
| Or | Loot, quêtes, vente d'objets | Recrutement, crafting, taverne |
| XP | Combats, quêtes | Montée de niveau des héros |
| Matériaux (Ferraille, Essence, Ficelle...) | Démontage d'objets | Crafting |
| Jetons de Boss | Événements mondiaux | Boutique spéciale (objets exclusifs) |
| Réputation de zone | Quêtes de zone | Débloquer les quêtes suivantes et les zones |

### Sinks (pour éviter l'inflation)

- Coût croissant de recrutement
- Coût de crafting en matériaux + or
- Objets consommables pour les quêtes actives (potions, parchemins)
- Réparation d'équipement (les objets WTF cassent facilement)
- Corruption du Narrateur (payer pour qu'il soit gentil pendant 10 minutes — il ne le sera pas vraiment)

---

## 6. Intégration IA — Règles et limites

### Budget API

Pour maîtriser les coûts (projet gratuit), l'IA est utilisée intelligemment :

| Fonctionnalité | Fréquence | Stratégie de cache |
|----------------|-----------|-------------------|
| Narration (texte) | Haute | Cache par type d'événement + contexte. Réutilisation si contexte similaire |
| Loot (texte + image) | Moyenne (Rare+ seulement) | Cache permanent par objet (jamais regénéré) |
| Quêtes quotidiennes (texte) | 3/jour/joueur | Pré-générer un pool de 50 quêtes par zone, rotation |
| Musique | Basse | Cache permanent, pool grandissant |
| Boss mondiaux | Très basse (1/3 jours) | Génération unique, cache permanent |

### Fallback

Si l'API Gemini est indisponible ou le budget dépassé :
- **Texte :** Fallback sur des templates statiques pré-écrits (humoristiques aussi)
- **Images :** Placeholder illustré générique par type d'objet
- **Musique :** Bibliothèque de morceaux libres de droits en backup

---

## 7. Progression et rétention

### Progression court terme (session)
- Loot satisfaisant à chaque connexion
- Quêtes quotidiennes (3/jour)
- Timer de donjon spécial (toutes les 8h)

### Progression moyen terme (semaine)
- Montée de niveaux des héros
- Déblocage de nouvelles zones
- Amélioration de l'équipement via crafting
- Événement de boss mondial (tous les 3 jours)

### Progression long terme (mois)
- Nouvelles zones débloquées
- Héros légendaires rares
- Collection de loot WTF
- Titres de boss mondiaux
- Zones générées par IA (contenu infini)

### Anti-plafonnement

- Le monde **s'étend** : de nouvelles zones sont ajoutées (manuellement + IA)
- Pas de reset / prestige — la progression est permanente
- Le scaling des ennemis suit la progression sans la bloquer
- Le contenu IA assure que le joueur ne voit jamais deux fois la même quête quotidienne

---

## 8. Interface utilisateur (écrans principaux)

1. **Dashboard** — Vue d'ensemble : équipe, zone en cours, ressources, feed du Narrateur
2. **Équipe** — Gestion des héros, équipement, talents
3. **Carte du monde** — Zones débloquées, exploration en cours, progression
4. **Donjon** — Vue du donjon en cours (salles, combat, loot)
5. **Quêtes** — Quêtes actives et disponibles, embranchements
6. **Forge de Gérard** — Crafting, fusion, démontage
7. **Taverne** — Recrutement + Barde musical + Journal des aventures
8. **Boss mondial** — Boss actuel, PV restants, classement des contributeurs
9. **Profil** — Stats, titres, collection, paramètres

---

## 9. Phases de développement

### Phase 1 — MVP (mois 1-2)
- Auth (login email, session unique)
- Création de héros (race + classe + trait)
- 1 zone d'exploration idle avec combats auto
- Système de loot (Commun à Rare, sans IA)
- Interface de base (Dashboard, Équipe, Carte)
- Narrateur avec templates statiques

### Phase 2 — Core features (mois 3-4)
- Recrutement (Taverne, équipe de 5)
- 3 zones supplémentaires
- Intégration Gemini texte (Narrateur IA, loot noms/descriptions)
- Quêtes à embranchements (pré-écrites)
- Crafting / Forge de Gérard
- Calcul offline

### Phase 3 — IA et social (mois 5-6)
- Intégration Gemini images (loot illustré)
- Intégration Gemini musique (Taverne musicale)
- Quêtes quotidiennes générées par IA
- Boss mondiaux
- Zones 5-8

### Phase 4 — Contenu infini (mois 7+)
- Zones générées par IA (9+)
- Boss générés par IA
- Ambiance musicale dynamique
- Événements saisonniers
- Polish, équilibrage, community feedback
