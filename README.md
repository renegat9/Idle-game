# 🏰 Le Donjon des Incompétents

## Idle RPG Fantasy Humoristique

> "Un idle game où tes héros sont nuls, ton loot est absurde, et le narrateur te déteste."

Inspiré de Kaamelott, Le Donjon de Naheulbeuk et Munchkin.

---

## 📋 Résumé du projet

Idle game web (navigateur) où le joueur recrute une équipe de héros incompétents (max 5) pour explorer des donjons, accomplir des quêtes absurdes et crafter des objets ridicules. Un narrateur sarcastique généré par IA commente chaque action. Le monde s'étend avec de nouvelles zones pour éviter le plafonnement. Modèle gratuit (hobby/portfolio).

---

## 🛠️ Stack technique

| Composant | Technologie |
|-----------|-------------|
| **Frontend** | React + TypeScript + Vite |
| **Backend** | PHP 8.2+ / Laravel |
| **Base de données** | MariaDB |
| **Cache** | Laravel File/DB Cache |
| **Auth** | Laravel Sanctum (tokens, session unique — un seul appareil connecté à la fois) |
| **IA — Texte** | API Gemini (narration, noms d'objets, dialogues, quêtes) |
| **IA — Images** | API Gemini Imagen (illustrations loot, monstres, héros) |
| **IA — Musique** | API Gemini MusicFX (ambiances taverne) |
| **Hébergement** | cPanel (VPS petit budget) — Apache, PHP natif, cron natif |
| **Communication** | API REST JSON + polling AJAX (pas de WebSockets sur cPanel) |

### Contraintes cPanel

- Pas de Docker, pas de Redis, pas de WebSockets
- Le temps réel est géré par polling AJAX (10-30 sec)
- Les jobs asynchrones passent par le cron Laravel (`php artisan schedule:run` toutes les minutes)
- Le calcul idle offline se fait à la reconnexion du joueur (pas de worker permanent)
- Le frontend est un build statique (Vite → dist/) servi par Apache
- L'API Laravel est sur un sous-domaine (api.tonsite.com → laravel/public/)

### Auth — Session unique

```php
// À chaque login : supprimer tous les anciens tokens puis en créer un nouveau
$user->tokens()->delete();
$token = $user->createToken('game-session')->plainTextToken;
```

Les anciennes sessions reçoivent un 401 → le frontend redirige vers le login avec un message thématique.

---

## 📁 Documents de game design

Le jeu est entièrement spécifié dans 8 documents. **Lis-les TOUS avant de coder.** Ils contiennent les formules exactes, les constantes paramétrables, les interactions entre systèmes et les cas limites.

### Ordre de lecture recommandé

| # | Document | Contenu | Dépendances |
|---|----------|---------|-------------|
| 1 | `GDD.md` | Vision globale, features, phases de dev, boucle de gameplay | Aucune — lire en premier |
| 2 | `COMBAT_SYSTEM.md` | Formules de combat (ATQ, DEF, critiques, initiative, XP, idle offline), stats de base des races/classes, table `game_settings` | GDD |
| 3 | `TALENT_TREES.md` | 8 classes × 3 branches × 7 talents = 168 talents. Coûts (1-3 pts), paliers (0/3/6), capstones | COMBAT_SYSTEM |
| 4 | `TRAITS_SYSTEM.md` | 10 traits négatifs détaillés : déclenchement, scaling par niveau, synergies avec la Branche du Défaut, simulation offline | COMBAT_SYSTEM, TALENT_TREES |
| 5 | `LOOT_CRAFTING.md` | Système de loot (raretés, génération, effets spéciaux), crafting (fusion, démontage, recettes), durabilité, slots d'équipement | COMBAT_SYSTEM |
| 6 | `BESTIARY.md` | 53 monstres, 8 mini-boss, 8 boss de zone (2 phases chacun), 5 boss mondiaux, système élémentaire (6 éléments), variantes élites (10 préfixes), compétences de monstres | COMBAT_SYSTEM, LOOT_CRAFTING |
| 7 | `QUESTS_EFFECTS.md` | Types de quêtes (zone, quotidienne, WTF, événementielle), 15 buffs, 10 debuffs, effets monde, réputation, relations PNJ, événements surprise, aventures idle | COMBAT_SYSTEM, BESTIARY |
| 8 | `ECONOMY.md` | Or (sources, dépenses, équilibrage), matériaux (génériques + par zone + cross-zone), boutiques (zone, taverne, Gérard, PNJ), enchantements, recettes complètes, anti-inflation | LOOT_CRAFTING, QUESTS_EFFECTS |
| 9 | `DATABASE.md` | 34 tables MariaDB, schéma SQL complet, relations, index, diagramme, notes de maintenance | TOUS les documents ci-dessus |

### Règles de design transversales

- **Valeurs entières uniquement.** Pas de float/double nulle part. Toutes les formules utilisent la division entière (floor). Les pourcentages sont appliqués via `stat × bonus / 100`.
- **Tout est paramétrable.** Chaque constante de gameplay est dans la table `game_settings` (clé/valeur). Modifier une valeur en base change le comportement du jeu sans redéployer.
- **Pas de conséquence négative permanente** pour le joueur. Les debuffs sont toujours temporaires (max 20 combats). Les héros perdus reviennent (max 60 min).
- **L'IA est un enrichissement, pas une dépendance.** Chaque appel Gemini a un fallback statique (templates pré-écrits, images placeholder, musique libre de droits). Le jeu fonctionne sans IA.
- **Cache agressif des contenus IA.** Une image générée pour un objet est stockée définitivement. Un commentaire du Narrateur est caché par type d'événement + contexte. La musique générée est stockée et réutilisée.

---

## 🏗️ Architecture Laravel

### Structure des dossiers

```
laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthController.php          # Login, logout, session unique
│   │   │   ├── Game/
│   │   │   │   ├── HeroController.php           # CRUD héros, recrutement, talents
│   │   │   │   ├── ExplorationController.php    # Exploration idle, calcul offline
│   │   │   │   ├── CombatController.php         # Résolution de combats (donjons, quêtes)
│   │   │   │   ├── InventoryController.php      # Inventaire, équipement, vente
│   │   │   │   ├── QuestController.php          # Quêtes, choix, progression
│   │   │   │   ├── CraftingController.php       # Forge de Gérard, fusion, démontage, recettes
│   │   │   │   ├── ShopController.php           # Boutiques, achats
│   │   │   │   ├── DungeonController.php        # Donjons spéciaux
│   │   │   │   ├── WorldBossController.php      # Boss mondiaux, contribution, classement
│   │   │   │   ├── TavernController.php         # Recrutement, musique, retrait debuffs
│   │   │   │   └── NarratorController.php       # Commentaires du Narrateur
│   │   │   └── Social/
│   │   │       ├── ReputationController.php     # Réputation de zone
│   │   │       └── NpcController.php            # Relations PNJ, cadeaux
│   │   └── Middleware/
│   │       └── EnsureSingleSession.php          # Vérifie token unique
│   ├── Models/
│   │   ├── User.php
│   │   ├── Hero.php
│   │   ├── Race.php
│   │   ├── GameClass.php                        # "Class" est réservé en PHP
│   │   ├── Trait_.php                           # "Trait" est réservé en PHP
│   │   ├── Talent.php
│   │   ├── Item.php
│   │   ├── ItemEffect.php
│   │   ├── Material.php
│   │   ├── Zone.php
│   │   ├── Monster.php
│   │   ├── Quest.php
│   │   ├── QuestStep.php
│   │   ├── QuestChoice.php
│   │   ├── Recipe.php
│   │   ├── WorldBoss.php
│   │   ├── Npc.php
│   │   └── GameSetting.php
│   ├── Services/
│   │   ├── CombatService.php                    # Moteur de combat (formules, tours, résolution)
│   │   ├── IdleService.php                      # Calcul offline, simulation simplifiée
│   │   ├── LootService.php                      # Génération de loot, raretés, stats
│   │   ├── CraftingService.php                  # Fusion, démontage, enchantement
│   │   ├── QuestService.php                     # Résolution des choix, tests de stats
│   │   ├── TraitService.php                     # Déclenchement et résolution des traits
│   │   ├── NarratorService.php                  # Génération/cache des commentaires
│   │   ├── GeminiService.php                    # Client API Gemini (texte, image, musique)
│   │   ├── ShopService.php                      # Génération de stock, prix
│   │   ├── WorldBossService.php                 # Gestion boss mondiaux, classement
│   │   └── SettingsService.php                  # Lecture/cache des game_settings
│   └── Jobs/
│       ├── GenerateNarratorComment.php          # Job asynchrone pour Gemini texte
│       ├── GenerateLootImage.php                # Job asynchrone pour Gemini image
│       ├── GenerateDailyQuests.php              # Pré-génération du pool de quêtes
│       ├── RefreshShopInventory.php             # Régénération des boutiques
│       ├── SpawnWorldBoss.php                   # Apparition boss mondial
│       └── CleanupLogs.php                      # Purge des logs anciens
├── database/
│   ├── migrations/                              # Voir DATABASE.md pour le schéma complet
│   └── seeders/
│       ├── RaceSeeder.php                       # 6 races
│       ├── ClassSeeder.php                      # 8 classes
│       ├── TraitSeeder.php                      # 10 traits
│       ├── TalentSeeder.php                     # 168 talents (8×3×7)
│       ├── ZoneSeeder.php                       # 8 zones
│       ├── MonsterSeeder.php                    # 53 monstres + 8 mini-boss + 8 boss
│       ├── MaterialSeeder.php                   # Matériaux génériques + par zone
│       ├── ItemTemplateSeeder.php               # ~150 objets prédéfinis
│       ├── RecipeSeeder.php                     # Toutes les recettes
│       ├── ElitePrefixSeeder.php                # 10 préfixes élites
│       ├── ElementChartSeeder.php               # Table des multiplicateurs élémentaires
│       ├── NpcSeeder.php                        # 8 PNJ
│       ├── WorldBossSeeder.php                  # 5 boss mondiaux
│       ├── SurpriseEventSeeder.php              # Événements surprise
│       ├── GameSettingsSeeder.php               # ~120 constantes paramétrables
│       └── QuestSeeder.php                      # Quêtes de zone pré-écrites
├── routes/
│   └── api.php                                  # Toutes les routes API REST
└── config/
    └── gemini.php                               # Config API Gemini (clé, modèles, limites)
```

### Routes API principales

```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout

GET    /api/game/dashboard                      # Vue d'ensemble (équipe, zone, ressources, narrateur)
GET    /api/game/poll                            # Polling pour mises à jour (événements, boss, etc.)

GET    /api/heroes                               # Liste des héros du joueur
POST   /api/heroes                               # Créer le premier héros
POST   /api/heroes/{id}/talents                  # Débloquer un talent
POST   /api/heroes/{id}/talents/reset            # Reset des talents
POST   /api/heroes/{id}/equip                    # Équiper un objet

GET    /api/exploration/status                   # État de l'exploration idle
POST   /api/exploration/start                    # Lancer l'exploration dans une zone
POST   /api/exploration/collect                  # Collecter les récompenses (+ calcul offline)

POST   /api/combat/dungeon/start                # Lancer un donjon
POST   /api/combat/dungeon/room/{id}            # Résoudre une salle
GET    /api/combat/log/{id}                     # Voir le replay d'un combat

GET    /api/inventory                            # Inventaire complet
POST   /api/inventory/sell                       # Vendre un objet
POST   /api/inventory/dismantle                  # Démonter un objet

GET    /api/quests                               # Quêtes disponibles
POST   /api/quests/{id}/start                   # Démarrer une quête
POST   /api/quests/{id}/choice                  # Faire un choix dans une quête

POST   /api/crafting/fuse                        # Fusion de 3 objets
POST   /api/crafting/recipe/{id}                # Crafter une recette
POST   /api/crafting/enchant                     # Enchanter un objet

GET    /api/shop/{zone_id}                       # Voir la boutique de zone
POST   /api/shop/buy                             # Acheter un objet
GET    /api/shop/tavern                          # Boutique de la Taverne
GET    /api/shop/gerard                          # Boutique de Gérard

POST   /api/tavern/recruit                       # Recruter un héros
POST   /api/tavern/remove-debuff                 # Payer pour retirer un debuff
GET    /api/tavern/music                         # Musique de la taverne

GET    /api/world-boss                           # Boss mondial actuel
POST   /api/world-boss/attack                   # Attaquer le boss mondial
GET    /api/world-boss/leaderboard              # Classement des contributeurs

GET    /api/reputation                           # Toutes les réputations du joueur
GET    /api/npcs                                 # Relations PNJ
POST   /api/npcs/{id}/gift                      # Offrir un cadeau

GET    /api/zones                                # Carte du monde
GET    /api/zones/{id}/progress                 # Progression dans une zone
```

---

## 🎮 Boucle de gameplay (résumé)

```
IDLE (automatique) :
  Héros explorent → combats auto → loot + XP → le joueur collecte

ACTIF (choix du joueur) :
  Quêtes à embranchements → choix narratifs → tests de stats → récompenses
  Crafting → fusion / recettes / enchantement
  Gestion d'équipe → talents, équipement, recrutement

OFFLINE (à la reconnexion) :
  Calcul de la progression écoulée (max 12h)
  Combats simulés via ratio de puissance
  Micro-événements idle générés
  
ÉVÉNEMENTS :
  Boss mondial tous les 3 jours → participation collective → classement → loot unique
  Quête WTF (5% /jour) → longue, absurde, très rémunératrice
```

---

## ⚔️ Moteur de combat (résumé des formules)

Toutes les formules sont dans `COMBAT_SYSTEM.md`. Voici les principales :

```
Initiative      = VIT + random(1, 20)
Esquive         = min(DEF × 100 / (DEF + VIT_atk + SPEED_BASE), DODGE_CAP)
Dégâts physiques = max(ATQ × variance / 100 × (100 - réduction) / 100, MIN_DAMAGE)
Réduction DEF   = min(DEF × 100 / (DEF + DEF_SOFT_CAP), DEF_HARD_CAP)
Dégâts magiques  = max(INT × variance / 100 × (100 - résistance/2) / 100, MIN_DAMAGE)
Critique chance  = min(CRIT_BASE_CHANCE + CHA / 4, CRIT_CAP)
Critique dégâts  = Dégâts × CRIT_DAMAGE_MULTIPLIER / 100
Élémentaire     = Dégâts_nets × Multiplicateur_élément / 100
XP par kill      = XP_BASE + (Niv_ennemi × XP_LEVEL_MULT) ± ajustement niveau
```

**Tout est en entiers. Division entière (floor). Pas de float.**

---

## 🤖 Intégration Gemini

### Utilisations

| Feature | API | Fréquence | Cache |
|---------|-----|-----------|-------|
| Narrateur sarcastique | Gemini Text | Haute | Par type d'événement + contexte |
| Noms/descriptions de loot Rare+ | Gemini Text | Moyenne | Permanent par objet |
| Illustrations de loot | Imagen | Moyenne | Permanent par objet |
| Quêtes quotidiennes | Gemini Text | Batch nocturne | Pool de 50/zone |
| Musique de taverne | MusicFX | Basse | Permanent, pool grandissant |
| Boss mondiaux IA (phase 2+) | Gemini Text + Imagen | Très basse | Permanent |

### Fallbacks (si API indisponible ou budget dépassé)

- **Texte :** Templates statiques pré-écrits (humour intégré)
- **Images :** Placeholder par slot + rareté
- **Musique :** Bibliothèque de morceaux libres de droits
- **Quêtes :** 30 templates statiques par zone

### Budget

Le `GeminiService` track chaque appel dans `ai_generation_log`. Un seuil configurable dans `game_settings` (`AI_DAILY_BUDGET_LIMIT`) coupe les appels non-essentiels quand le budget est atteint.

---

## 🗄️ Base de données

34 tables MariaDB. Schéma SQL complet dans `DATABASE.md`.

### Groupes principaux

- **Config :** `game_settings` (~120 constantes paramétrables)
- **Users :** `users`, `personal_access_tokens`
- **Héros :** `races`, `classes`, `traits`, `heroes`, `talents`, `hero_talents`, `hero_buffs`
- **Inventaire :** `items`, `item_effects`, `item_templates`, `materials`, `user_materials`
- **Monde :** `zones`, `monsters`, `monster_skills`, `elite_prefixes`, `encounter_groups`, `element_chart`
- **Quêtes :** `quests`, `quest_steps`, `quest_choices`, `user_quest_progress`, `user_daily_quests`
- **Social :** `npcs`, `user_npc_relations`, `user_reputations`, `world_bosses`, `world_boss_contributions`
- **Crafting :** `recipes`, `recipe_ingredients`, `user_recipes`
- **Boutiques :** `shop_inventories`
- **Exploration :** `user_exploration`, `user_zone_progress`, `dungeon_instances`, `idle_event_log`
- **IA :** `narrator_cache`, `ai_generation_log`, `tavern_music`
- **Logs :** `combat_log`, `economy_log`
- **Événements :** `surprise_events`

### Conventions DB

- Tous les IDs : `BIGINT UNSIGNED AUTO_INCREMENT`
- Dates : `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`
- **Aucun FLOAT/DOUBLE** — tout en `INT`
- FK avec `ON DELETE CASCADE` sauf mention contraire
- JSON pour les données flexibles (effets, mécaniques, compositions)
- Index sur toutes les requêtes critiques (voir section 18 de DATABASE.md)

---

## 📅 Phases de développement

### Phase 1 — MVP (mois 1-2)

- [ ] Setup Laravel + MariaDB + migrations + seeders
- [ ] Auth (register, login, session unique Sanctum)
- [ ] Modèles et relations Eloquent
- [ ] Création de héros (race + classe + trait)
- [ ] Zone 1 (Prairie) — exploration idle basique
- [ ] Moteur de combat (formules complètes)
- [ ] Système de loot (Commun à Rare, sans IA)
- [ ] Inventaire, équipement, vente
- [ ] Calcul offline à la reconnexion
- [ ] Narrateur avec templates statiques
- [ ] Frontend React : Dashboard, Équipe, Carte, Inventaire
- [ ] API REST complète pour les features ci-dessus

### Phase 2 — Core features (mois 3-4)

- [ ] Recrutement (Taverne, max 5 héros)
- [ ] Système de talents (déblocage, reset payant)
- [ ] Zones 2-4 avec leurs monstres, boss, mini-boss
- [ ] Quêtes de zone (pré-écrites, embranchements)
- [ ] Crafting / Forge de Gérard (fusion, démontage, recettes)
- [ ] Boutiques (zone, taverne, Gérard)
- [ ] Donjons spéciaux (timer 8h)
- [ ] Système élémentaire (faiblesses/résistances)
- [ ] Variantes élites
- [ ] Enchantements

### Phase 3 — IA et social (mois 5-6)

- [ ] Intégration Gemini texte (Narrateur IA, noms de loot)
- [ ] Intégration Gemini images (illustrations loot Rare+)
- [ ] Intégration Gemini musique (Taverne musicale)
- [ ] Quêtes quotidiennes (pool IA + fallback)
- [ ] Boss mondiaux (5 pré-définis)
- [ ] Système de réputation et relations PNJ
- [ ] Boutiques PNJ (débloquées par relation)
- [ ] Événements surprise (quêtes + idle)
- [ ] Zones 5-8

### Phase 4 — Contenu infini (mois 7+)

- [ ] Zones 9+ générées par IA
- [ ] Boss mondiaux générés par IA
- [ ] Quêtes WTF
- [ ] Ambiance musicale dynamique (contexte)
- [ ] Événements saisonniers
- [ ] Polish, équilibrage, feedback communautaire

---

## 🔧 Commandes utiles

```bash
# Installation
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed

# Développement
php artisan serve                               # Serveur local
php artisan schedule:run                        # Exécuter les jobs planifiés
php artisan queue:work                          # Traiter la file d'attente

# Seeders individuels
php artisan db:seed --class=GameSettingsSeeder
php artisan db:seed --class=RaceSeeder
php artisan db:seed --class=ClassSeeder
php artisan db:seed --class=TraitSeeder
php artisan db:seed --class=TalentSeeder
php artisan db:seed --class=ZoneSeeder
php artisan db:seed --class=MonsterSeeder
php artisan db:seed --class=MaterialSeeder
php artisan db:seed --class=ElementChartSeeder

# Maintenance
php artisan logs:cleanup                        # Purge des logs anciens
php artisan shop:refresh                        # Régénérer les boutiques
php artisan quests:generate                     # Remplir le pool de quêtes IA
php artisan world-boss:spawn                    # Forcer l'apparition d'un boss mondial
```

---

## ⚠️ Points d'attention pour le développement

1. **Le `SettingsService` est critique.** Il charge et cache les `game_settings`. Toutes les formules doivent passer par ce service, jamais utiliser de valeurs en dur dans le code.

2. **Le `CombatService` est le cœur du jeu.** Il doit implémenter exactement les formules de `COMBAT_SYSTEM.md`. Chaque calcul utilise la division entière. Tester avec des cas limites (DEF très haute, ATQ à 0, etc.).

3. **La validation des sorties Gemini est obligatoire.** Ne jamais injecter une sortie IA dans le jeu sans vérifier la structure JSON, les IDs d'effets autorisés, les durées max des debuffs, etc. Voir `QUESTS_EFFECTS.md` section 7.2.

4. **Les traits négatifs interagissent avec les talents.** L'ordre de résolution est : effets de statut → jet de trait → talents Branche du Défaut → action. Voir `TRAITS_SYSTEM.md` section 3.

5. **Le calcul offline ne doit PAS simuler tour par tour.** Utiliser le ratio de puissance simplifié (voir `COMBAT_SYSTEM.md` section 6) pour performance.

6. **Les logs (combat, économie, idle) doivent être purgés.** Sans purge, la base explose. Le cron `CleanupLogs` est essentiel.

7. **Tester l'équilibre économique.** Le tableau de `ECONOMY.md` section 4.2 donne les ratios cibles. Si un joueur peut tout acheter sans effort, les sinks sont insuffisants.
