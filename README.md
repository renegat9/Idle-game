# 🏰 Le Donjon des Incompétents

## Idle RPG Fantasy Humoristique

> *"Un idle game où tes héros sont nuls, ton loot est absurde, et le narrateur te déteste."*

Inspiré de Kaamelott, Le Donjon de Naheulbeuk et Munchkin.

---

## 📊 État d'avancement

**Phases 1–4 complètes** — 184 tests, 0 échec.

| Phase | Contenu | Statut |
|-------|---------|--------|
| **1 — MVP** | Auth, héros, combat, loot, inventaire, exploration idle, calcul offline, Narrateur statique | ✅ Complet |
| **2 — Core** | Quêtes, crafting (Forge de Gérard), taverne, recrutement, boutique, donjons, 4 zones | ✅ Complet |
| **3 — Systèmes** | Boutique, donjon spécial, boss mondial, arbres de talents (168 talents) | ✅ Complet |
| **4 — IA & Async** | GeminiService (texte/image/musique + fallbacks), Jobs async, cron, `logs:cleanup`, profil | ✅ Complet |
| **5 — Contenu** | Zones 5-8, quêtes WTF, réputation, PNJ, événements saisonniers | 🔲 À venir |

---

## 📋 Résumé du projet

Idle game web où le joueur recrute une équipe de héros incompétents (max 5) pour explorer des donjons, accomplir des quêtes absurdes et crafter des objets ridicules. Un narrateur sarcastique — généré par IA si `AI_ENABLED=1`, templates statiques sinon — commente chaque action. Modèle gratuit (hobby/portfolio).

---

## 🛠️ Stack technique

| Composant | Technologie |
|-----------|-------------|
| **Frontend** | React 19 + TypeScript + Vite |
| **Backend** | PHP 8.2 / Laravel 12 |
| **Base de données** | MariaDB (SQLite en test) |
| **Cache** | Laravel File/DB Cache |
| **Auth** | Laravel Sanctum — session unique (un token actif à la fois) |
| **IA — Texte** | Gemini API `gemini-2.0-flash` (narration, loot, quêtes) |
| **IA — Images** | Gemini Imagen `imagen-3.0-generate-002` (illustrations loot) |
| **IA — Musique** | MusicFX via Gemini (ambiances taverne) |
| **Hébergement** | cPanel — Apache, PHP natif, cron natif (pas de Docker/Redis/WS) |
| **Communication** | API REST JSON + polling AJAX toutes les 10–30 s |

---

## 🔧 Installation & commandes

```bash
# Backend
cd laravel/
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed

# Frontend
cd frontend/
npm install
npm run dev      # dev server
npm run build    # build statique → dist/

# Développement backend
php artisan serve
php artisan schedule:run    # jobs planifiés (à mettre en cron toutes les minutes)
php artisan queue:work      # file d'attente async

# Seeders individuels
php artisan db:seed --class=GameSettingsSeeder   # ~120 constantes paramétrables
php artisan db:seed --class=RaceSeeder           # 6 races
php artisan db:seed --class=ClassSeeder          # 8 classes
php artisan db:seed --class=TraitSeeder          # 10 traits négatifs
php artisan db:seed --class=TalentSeeder         # 168 talents (8 classes × 3 branches × 7)
php artisan db:seed --class=ZoneSeeder           # 4 zones
php artisan db:seed --class=MonsterSeeder        # monstres, mini-boss, boss de zone

# Maintenance (aussi déclenchés par le cron)
php artisan logs:cleanup        # purge combat_log (>30j), economy_log (>90j), idle_event_log (>7j)
php artisan shop:refresh        # purge les items de boutique expirés
php artisan quests:generate     # génère le pool de quêtes quotidiennes (Gemini + fallback)
php artisan world-boss:spawn    # invoque un boss mondial si aucun n'est actif

# Tests
php artisan test
php artisan test --filter=ShopTest   # test d'un module spécifique
```

---

## 🏗️ Architecture

### Backend (`laravel/`)

```
app/
├── Http/Controllers/Game/
│   ├── AuthController.php            # register / login (session unique) / logout
│   ├── DashboardController.php       # GET /game/dashboard, GET /game/poll
│   ├── HeroController.php            # CRUD héros, équipement
│   ├── TalentController.php          # arbre de talents, allocation, reset
│   ├── ExplorationController.php     # exploration idle, collecte, offline
│   ├── InventoryController.php       # inventaire, vente
│   ├── QuestController.php           # quêtes, choix narratifs
│   ├── CraftingController.php        # Forge de Gérard : fusion, démontage, recettes
│   ├── TavernController.php          # recrutement, retrait de debuffs
│   ├── ShopController.php            # boutique de zone, rotation 6h
│   ├── DungeonController.php         # donjon spécial, salles, abandon
│   ├── WorldBossController.php       # boss mondial, attaque, leaderboard
│   ├── ZoneController.php            # carte du monde, progression
│   └── ProfileController.php         # profil, stats, historique économique
├── Services/
│   ├── CombatService.php             # moteur de combat complet (formules COMBAT_SYSTEM.md)
│   ├── IdleService.php               # calcul offline via ratio de puissance (pas turn-by-turn)
│   ├── LootService.php               # génération de loot par rareté
│   ├── CraftingService.php           # fusion, démontage, recettes, enchantements
│   ├── QuestService.php              # résolution des choix, tests de stats
│   ├── TraitService.php              # déclenchement et résolution des traits négatifs
│   ├── NarratorService.php           # commentaires IA (GeminiService) + cache narrator_cache
│   ├── GeminiService.php             # client Gemini : text/image/music + fallbacks statiques
│   ├── ShopService.php               # génération de stock, markup, rotation
│   ├── TalentService.php             # arbre de talents, allocation, reset
│   ├── WorldBossService.php          # boss mondiaux, contributions, classement
│   └── SettingsService.php           # lit et cache game_settings — TOUTES les constantes passent ici
├── Jobs/
│   ├── CleanupLogs.php               # purge périodique des logs (occurred_at)
│   ├── GenerateNarration.php         # appel Gemini async pour narration
│   ├── RefreshShop.php               # purge des items de boutique expirés
│   └── GenerateDailyQuests.php       # génération du pool de quêtes quotidiennes
├── Console/Commands/
│   ├── SpawnWorldBoss.php            # world-boss:spawn
│   ├── CleanupLogsCommand.php        # logs:cleanup
│   ├── RefreshShopCommand.php        # shop:refresh
│   └── GenerateQuestsCommand.php     # quests:generate
└── Models/
    User, Hero, Race, GameClass, Trait_, Talent, HeroTalent, HeroBuff,
    Item, ItemEffect, ItemTemplate, Material, Zone, Monster, MonsterSkill,
    Quest, QuestStep, UserQuest, Recipe, ShopInventory, Dungeon,
    WorldBoss, BossContribution, TavernRecruit, NarratorCache,
    GameSetting, UserExploration, UserZoneProgress, IdleEventLog, EncounterGroup
```

**Note :** `GameClass` (pas `Class`) et `Trait_` (pas `Trait`) — ces noms sont réservés en PHP.

**Note :** `Talent` et `HeroTalent` déclarent explicitement `$table` car le pluraliseur Laravel retourne `talent` au lieu de `talents`.

### Frontend (`frontend/`)

```
src/
├── pages/
│   ├── LoginPage.tsx / RegisterPage.tsx
│   ├── DashboardPage.tsx      # vue d'ensemble, collecte offline
│   ├── TeamPage.tsx           # gestion d'équipe
│   ├── MapPage.tsx            # carte du monde, exploration
│   ├── InventoryPage.tsx      # inventaire, équipement
│   ├── QuestPage.tsx          # quêtes disponibles et en cours
│   ├── ForgePage.tsx          # Forge de Gérard (crafting)
│   ├── TavernPage.tsx         # recrutement, taverne
│   ├── ShopPage.tsx           # boutique de zone
│   ├── DungeonPage.tsx        # donjon spécial
│   ├── WorldBossPage.tsx      # boss mondial, leaderboard
│   ├── TalentsPage.tsx        # arbres de talents des héros
│   └── ProfilePage.tsx        # stats, historique économique, préfs narrateur
├── api/
│   ├── client.ts              # axios avec Bearer token
│   ├── auth.ts                # register / login / logout
│   └── game.ts                # tous les appels jeu (dashboard, heroes, shop, dungeon…)
├── store/
│   ├── authStore.ts           # zustand — user, token
│   └── gameStore.ts           # zustand — heroes, gold, exploration
└── components/
    ├── layout/AppShell.tsx    # navigation principale
    ├── narrator/              # bulle de narration
    ├── hero/                  # cartes héros
    ├── exploration/           # barre de progression
    └── inventory/             # slots d'inventaire
```

---

## 🗺️ Routes API

```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout                          (auth requise)

GET    /api/game/dashboard                       vue d'ensemble + calcul offline
GET    /api/game/poll                            polling événements (10-30s)

GET    /api/heroes                               équipe du joueur
POST   /api/heroes                               créer le héros initial
POST   /api/heroes/{id}/equip                    équiper un objet
GET    /api/heroes/{id}/talents                  arbre de talents
POST   /api/heroes/{id}/talents/{tid}/allocate   débloquer un talent
POST   /api/heroes/{id}/talents/reset            réinitialiser (coût en or)

GET    /api/exploration/status                   état de l'exploration idle
POST   /api/exploration/start                    lancer l'exploration
POST   /api/exploration/collect                  collecter + calcul offline

GET    /api/inventory                            inventaire complet
POST   /api/inventory/sell                       vendre un objet

GET    /api/zones                                carte du monde

GET    /api/quests                               quêtes disponibles
POST   /api/quests/{id}/start                    démarrer une quête
POST   /api/user-quests/{id}/choose              faire un choix narratif

GET    /api/crafting                             recettes disponibles + matériaux
POST   /api/crafting/fuse                        fusionner 3 objets
POST   /api/crafting/dismantle                   démonter un objet
POST   /api/crafting/craft                       crafter via recette

GET    /api/tavern                               recrues disponibles + héros avec debuffs
POST   /api/tavern/hire/{recruitId}              recruter
POST   /api/tavern/remove-debuff                 retirer un debuff (payant)

GET    /api/shop?zone_id={id}                    boutique de zone (rotation 6h)
POST   /api/shop/buy                             acheter un item

GET    /api/dungeon                              état du donjon actif
POST   /api/dungeon/start                        lancer un donjon
POST   /api/dungeon/{id}/enter                   entrer dans la salle suivante
POST   /api/dungeon/{id}/abandon                 abandonner

GET    /api/world-boss                           boss mondial actif
POST   /api/world-boss/attack                    attaquer (cooldown 5 min)
GET    /api/world-boss/leaderboard               classement des contributeurs

GET    /api/profile                              stats, historique économique, budget IA
PATCH  /api/profile                              modifier préférences (narrator_frequency)

GET    /api/reference/races
GET    /api/reference/classes
GET    /api/reference/traits
```

---

## ⚔️ Formules de combat (résumé)

> **Règle absolue : tout est en entiers. Division floor. Pas de float.**

```
Initiative       = VIT + random(1, 20)
Réduction DEF    = min(DEF × 100 / (DEF + DEF_SOFT_CAP), DEF_HARD_CAP)
Esquive (%)      = min(DEF × 100 / (DEF + VIT_atk + SPEED_BASE), DODGE_CAP)
Dégâts physiques = max(ATQ × variance / 100 × (100 - réduction) / 100, MIN_DAMAGE)
Dégâts magiques  = max(INT × variance / 100 × (100 - résistance/2) / 100, MIN_DAMAGE)
Crit chance (%)  = min(CRIT_BASE_CHANCE + CHA / 4, CRIT_CAP)
Dégâts crit      = Dégâts × CRIT_DAMAGE_MULTIPLIER / 100
Élémentaire      = Dégâts_nets × Multiplicateur / 100
XP par kill      = XP_BASE + (Niv_ennemi × XP_LEVEL_MULT) ± ajust. niveau
Offline          = ratio de puissance simplifié, cap 12h (pas tour par tour)
```

Formules complètes dans `COMBAT_SYSTEM.md`.

---

## 🤖 Intégration Gemini

Le `GeminiService` vérifie `AI_ENABLED` et `AI_DAILY_BUDGET_LIMIT` (table `game_settings`) avant chaque appel. Chaque appel est tracé dans `ai_generation_log`. **Fallback statique garanti** — le jeu fonctionne sans clé API.

| Fonctionnalité | Fréquence | Cache | Fallback |
|----------------|-----------|-------|---------|
| Narration (texte) | Haute | `narrator_cache` par type+contexte | Templates statiques humoristiques |
| Noms/descriptions de loot Rare+ | Moyenne | Permanent par objet | Templates génériques |
| Illustrations de loot | Moyenne | Permanent par objet | Placeholders par slot+rareté |
| Quêtes quotidiennes | Batch nocturne | Pool 15/zone/jour | Templates par zone |
| Musique de taverne | Basse | `tavern_music`, pool grandissant | Bibliothèque libre de droits |
| Description de boss | Très basse | Permanent | Template statique |

```bash
# Activer l'IA (désactivée par défaut)
php artisan tinker --execute="DB::table('game_settings')->where('setting_key','AI_ENABLED')->update(['setting_value'=>1]);"

# Configurer la clé API dans .env
GEMINI_API_KEY=your_key_here
```

---

## 🗄️ Base de données

37 tables MariaDB. Schéma SQL complet dans `DATABASE.md`.

### Groupes

| Groupe | Tables |
|--------|--------|
| Config | `game_settings` (~120 constantes paramétrables) |
| Auth | `users`, `personal_access_tokens` |
| Héros | `races`, `classes`, `traits`, `heroes`, `hero_buffs`, `talents`, `hero_talents` |
| Inventaire | `items`, `item_effects`, `item_templates`, `materials`, `user_materials` |
| Monde | `zones`, `monsters`, `monster_skills`, `elite_prefixes`, `encounter_groups`, `element_chart` |
| Quêtes | `quests`, `quest_steps`, `user_quests`, `user_daily_quests`, `user_zone_progress` |
| Exploration | `user_exploration`, `idle_event_log` |
| Boutiques | `shop_inventories` |
| Donjons | `dungeons` |
| Boss | `world_bosses`, `boss_contributions` |
| Crafting | `recipes`, `user_recipes` |
| Taverne | `tavern_recruits` |
| IA | `narrator_cache`, `ai_generation_log`, `tavern_music` |
| Logs | `combat_log`, `economy_log` |

### Conventions

- IDs : `BIGINT UNSIGNED AUTO_INCREMENT`
- Timestamps des logs : `occurred_at` (pas `created_at`)
- **Aucun `FLOAT`/`DOUBLE`** — tout en `INT`
- FK `ON DELETE CASCADE` sauf mention contraire
- Colonnes JSON pour les données flexibles (effets, mécaniques, loot)

---

## ⏱️ Cron schedule

Configuré dans `routes/console.php`, exécuté via `php artisan schedule:run` (à placer en crontab toutes les minutes).

```
* * * * *   php artisan schedule:run   # crontab entry

00:00 quotidien  → logs:cleanup        purge des logs anciens
00:05 quotidien  → quests:generate     pool de quêtes quotidiennes (Gemini + fallback)
toutes les 6h    → shop:refresh        purge des items de boutique expirés
tous les 3j 12h  → world-boss:spawn    invocation d'un nouveau boss mondial
```

---

## 📝 Documents de design

| Document | Contenu |
|----------|---------|
| `GDD.md` | Vision, features, boucle de gameplay, phases |
| `COMBAT_SYSTEM.md` | Formules exactes, stats races/classes, simulation offline |
| `TALENT_TREES.md` | 8 classes × 3 branches × 7 talents = 168 talents |
| `TRAITS_SYSTEM.md` | 10 traits négatifs, déclenchement, synergies Branche du Défaut |
| `LOOT_CRAFTING.md` | Raretés, génération, effets spéciaux, fusion, durabilité |
| `BESTIARY.md` | 53 monstres, 8 boss de zone, 5 boss mondiaux, système élémentaire |
| `QUESTS_EFFECTS.md` | Types de quêtes, 15 buffs, 10 debuffs, événements surprise |
| `ECONOMY.md` | Or (sources/dépenses), matériaux, boutiques, équilibrage |
| `DATABASE.md` | 37 tables MariaDB, schéma SQL, index, diagramme |

---

## ⚠️ Points d'attention

1. **`SettingsService` est critique.** Toutes les constantes de gameplay passent par ce service. Ne jamais hardcoder une valeur de jeu dans le code.

2. **`CombatService` implémente les formules exactes de `COMBAT_SYSTEM.md`.** Division entière partout. Tester les cas limites (DEF très haute, ATQ à 0, traits actifs).

3. **Fallbacks Gemini obligatoires.** Chaque appel `GeminiService` a un fallback statique. Valider la structure JSON des sorties Gemini avant injection (voir `QUESTS_EFFECTS.md` § 7.2).

4. **Calcul offline ≠ simulation tour par tour.** Utiliser le ratio de puissance simplifié (`IdleService`). Cap à 12 heures.

5. **Les logs doivent être purgés.** `combat_log`, `economy_log`, `idle_event_log` croissent rapidement. Le cron `logs:cleanup` est non optionnel.

6. **`HeroTalent` et `Talent` déclarent `protected $table`.** Le pluraliseur Laravel retourne `hero_talent`/`talent` (sans s) — toujours préciser la table explicitement pour ces modèles.
