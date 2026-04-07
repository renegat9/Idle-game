# Le Donjon des Incompétents

> *"Un idle game où tes héros sont nuls, ton loot est absurde, et le narrateur te déteste."*

Idle RPG web humoristique inspiré de Kaamelott, Le Donjon de Naheulbeuk et Munchkin.  
Le joueur gère une équipe de héros incompétents qui explorent des donjons, accomplissent des quêtes absurdes et craftent des objets ridicules — commentés à tout moment par un narrateur sarcastique alimenté par Gemini AI.

---

## Stack technique

| Composant | Technologie |
|-----------|-------------|
| Frontend | React 19 + TypeScript + Vite |
| Backend | PHP 8.3 / Laravel 13 |
| Base de données | MariaDB (SQLite en test) |
| Auth | Laravel Sanctum — token unique par session |
| Cache | Laravel File Cache |
| IA | Gemini API (texte, Imagen 3, MusicFX) |
| Communication | REST JSON + polling AJAX toutes les 10–30 s |
| Hébergement cible | cPanel — Apache, PHP natif, cron natif |

---

## Démarrage rapide

```bash
# Backend
cd laravel/
composer install
cp .env.example .env
# Renseigner DB_* et GEMINI_API_KEY dans .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve

# Frontend (dans un autre terminal)
cd frontend/
npm install
npm run dev
```

> Pour le déploiement en production sur cPanel, voir [`INSTALL_CPANEL.md`](INSTALL_CPANEL.md).

---

## Fonctionnalités

### Gestion d'équipe
- Recrutement de héros (race × classe × trait négatif) à la taverne
- Héros légendaires rares (~10% de chance) avec biographie générée par Gemini
- Équipe de 5 héros maximum, renvoi possible à tout moment
- Équipement par slot (arme, armure, casque, bottes, accessoire, truc bizarre)
- Arbres de talents : 8 classes × 3 branches × 7 talents = 168 talents

### Traits négatifs & Synergies cachées
10 traits négatifs (Couard, Narcoleptique, Kleptomane, Pyromane…) qui se déclenchent aléatoirement en combat.  
Certaines combinaisons classe + trait créent des **synergies cachées** à découvrir :

| Synergie | Effet |
|----------|-------|
| Voleur + Kleptomane | +50% loot |
| Barbare + Pyromane | +30% ATQ |
| Barde + Narcoleptique | −40% VIT ennemis |
| Prêtre + Couard | +25% DEF |
| Mage + Philosophe | +40% INT |
| Nécromancien + Pacifiste | +20% loot |
| Ranger + Mythomane | +20% ATQ, +15% esquive |

### Exploration idle
- Exploration en temps réel avec calcul offline (ratio de puissance, cap 12h)
- 8 zones prédéfinies (niveaux 1–80) + zones procédurales générées par Gemini (9+)
- Système de réputation par zone (5 paliers, bonus de loot jusqu'à +50%)
- Événements saisonniers avec modificateurs de XP/or/loot (ex: Semaine de la Forge, Halloween Raté)

### Combat
- Résolution automatique (formules exactes : initiative, esquive, élémentaire, critique)
- 7 éléments avec table des affinités (feu > glace, etc.)
- Monstres élites avec préfixes modificateurs
- Synergies de traits appliquées à chaque combat

### Quêtes
- Quêtes narratives à choix multiples (tests de stats INT/CHA/ATQ…)
- 3 quêtes quotidiennes générées par Gemini (renouvelées chaque jour)
- Résultats variables : succès, échec, succès partiel, événements surprise

### Forge de Gérard (crafting)
- Fusion de 3 objets → objet supérieur
- Démontage → matériaux
- Recettes spéciales avec ingrédients
- Enchantements (jusqu'à 3 par objet, coût croissant en matériaux + or)

### Consommables
6 types de consommables (potions de soin, parchemins d'XP, antidote…) :
- Effet immédiat sur toute l'équipe active
- Stack max 99 par type
- Achetables en boutique ou trouvés en exploration

### Boss Mondial
- Boss partagé entre tous les joueurs, réinitialisé tous les 3 jours
- Attaques manuelles (cooldown 5 min) + **attaques NPC automatiques toutes les 2h**
- Description et mécanique spéciale générées par Gemini
- Classement des contributeurs avec récompenses en or

### Taverne & Musique
- Recrutement de héros (rotation 24h), retrait de debuffs payant
- Musique contextuelle dynamique (boss > quête en cours > victoire > taverne)

### Narrateur sarcastique
- Commentaires sur chaque action, mis en cache
- Alimenté par Gemini si `AI_ENABLED=1`, templates statiques sinon
- Fréquence configurable par le joueur (silencieux / normal / bavard)

---

## Architecture

### Backend (`laravel/`)

```
app/
├── Http/Controllers/Game/
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── HeroController.php          # CRUD, équipement, renvoi, synergies
│   ├── TalentController.php
│   ├── ExplorationController.php
│   ├── InventoryController.php
│   ├── ConsumableController.php    # inventaire & utilisation consommables
│   ├── QuestController.php         # quêtes + quotidiennes
│   ├── CraftingController.php      # fusion, démontage, recettes, enchantements
│   ├── TavernController.php        # recrutement, héros légendaires, musique
│   ├── ShopController.php
│   ├── DungeonController.php
│   ├── WorldBossController.php
│   ├── ZoneController.php
│   ├── ReputationController.php
│   ├── MusicController.php         # musique contextuelle dynamique
│   ├── SeasonalEventController.php # événements saisonniers
│   └── ProfileController.php
│
├── Services/
│   ├── CombatService.php           # moteur de combat (formules COMBAT_SYSTEM.md)
│   ├── IdleService.php             # calcul offline ratio de puissance
│   ├── LootService.php             # génération loot + dispatch image IA
│   ├── CraftingService.php         # fusion, démontage, enchantements
│   ├── ConsumableService.php       # effets consommables (soin, XP, or, cure)
│   ├── QuestService.php            # résolution choix, quêtes quotidiennes
│   ├── TraitService.php            # déclenchement traits + synergies cachées
│   ├── TalentService.php
│   ├── ShopService.php
│   ├── WorldBossService.php
│   ├── ZoneGeneratorService.php    # génération procédurale zones 9+
│   ├── SeasonalEventService.php    # détection événements, modificateurs
│   ├── NarratorService.php         # commentaires IA + cache
│   ├── GeminiService.php           # client Gemini texte/image/musique + fallbacks
│   └── SettingsService.php         # TOUTES les constantes via game_settings
│
├── Jobs/
│   ├── GenerateLootImage.php       # image Imagen 3 async pour loot Rare+
│   ├── GenerateDailyQuests.php     # pool quêtes quotidiennes
│   ├── CleanupLogs.php
│   └── RefreshShop.php
│
└── Console/Commands/
    ├── SpawnWorldBossCommand.php
    ├── WorldBossAutoAttackCommand.php  # attaques NPC simulées toutes les 2h
    ├── GenerateZoneCommand.php          # zones:generate
    ├── CleanupLogsCommand.php
    ├── RefreshShopCommand.php
    └── GenerateQuestsCommand.php
```

### Frontend (`frontend/src/`)

```
pages/
├── DashboardPage.tsx      # vue d'ensemble + bannière événements saisonniers
├── TeamPage.tsx           # équipe, synergies actives, renvoi de héros
├── MapPage.tsx            # carte + réputation par zone
├── InventoryPage.tsx      # inventaire, images IA, badge IA
├── ConsumablesPage.tsx    # consommables, utilisation directe
├── QuestPage.tsx          # quêtes de zone + quotidiennes
├── ForgePage.tsx          # fusion, démontage, recettes, enchantements
├── TavernPage.tsx         # recrues, légendaires, musique contextuelle
├── ShopPage.tsx
├── DungeonPage.tsx
├── WorldBossPage.tsx      # boss, description IA, leaderboard
├── TalentsPage.tsx
└── ProfilePage.tsx

store/
├── authStore.ts           # zustand — user, token
└── gameStore.ts           # zustand — heroes, gold, offline result
```

---

## Routes API

```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout

GET    /api/game/dashboard
GET    /api/game/poll

GET    /api/heroes
GET    /api/heroes/synergies            synergies actives de l'équipe
POST   /api/heroes                      créer un héros
POST   /api/heroes/{hero}/equip
DELETE /api/heroes/{hero}               renvoyer un héros

GET    /api/heroes/{id}/talents
POST   /api/heroes/{id}/talents/{tid}/allocate
POST   /api/heroes/{id}/talents/reset

GET    /api/exploration/status
POST   /api/exploration/start
POST   /api/exploration/collect

GET    /api/inventory
POST   /api/inventory/sell

GET    /api/consumables                 inventaire consommables
GET    /api/consumables/catalog
POST   /api/consumables/{slug}/use

GET    /api/zones
GET    /api/reputation
GET    /api/reputation/{zoneId}

GET    /api/quests
GET    /api/quests/daily
POST   /api/quests/{id}/start
POST   /api/user-quests/{id}/choose

GET    /api/crafting
POST   /api/crafting/fuse
POST   /api/crafting/dismantle
POST   /api/crafting/craft
GET    /api/crafting/enchantments
POST   /api/crafting/enchant

GET    /api/tavern
GET    /api/tavern/music
POST   /api/tavern/hire/{recruitId}
POST   /api/tavern/remove-debuff

GET    /api/shop
POST   /api/shop/buy

GET    /api/dungeon
POST   /api/dungeon/start
POST   /api/dungeon/{id}/enter
POST   /api/dungeon/{id}/abandon

GET    /api/world-boss
POST   /api/world-boss/attack
GET    /api/world-boss/leaderboard

GET    /api/events/current
GET    /api/events

GET    /api/music/current

GET    /api/profile
PATCH  /api/profile

GET    /api/reference/races
GET    /api/reference/classes
GET    /api/reference/traits
```

---

## Commandes Artisan

```bash
# Installation
php artisan key:generate
php artisan migrate
php artisan db:seed

# Seeders individuels
php artisan db:seed --class=GameSettingsSeeder   # ~120 constantes paramétrables
php artisan db:seed --class=RaceSeeder           # 6 races
php artisan db:seed --class=ClassSeeder          # 8 classes
php artisan db:seed --class=TraitSeeder          # 10 traits négatifs
php artisan db:seed --class=TalentSeeder         # 168 talents
php artisan db:seed --class=MonsterSeeder        # monstres et boss de zone
php artisan db:seed --class=ConsumableSeeder     # 6 consommables
php artisan db:seed --class=SeasonalEventSeeder  # 5 événements saisonniers

# Maintenance (aussi déclenchés par le scheduler)
php artisan logs:cleanup           # purge combat_log / economy_log / idle_event_log
php artisan shop:refresh           # purge items boutique expirés
php artisan quests:generate        # pool quêtes quotidiennes (Gemini + fallback)
php artisan world-boss:spawn       # invoque un boss si aucun n'est actif
php artisan world-boss:auto-attack # attaques NPC simulées sur le boss actif
php artisan zones:generate         # génère une nouvelle zone procédurale (9+)

# Tests
php artisan test                            # suite complète (255 tests)
php artisan test --filter=CombatTest        # module spécifique
```

---

## Scheduler (Cron)

Entrée unique dans la crontab cPanel :

```
* * * * * /usr/local/bin/php /chemin/vers/laravel/artisan schedule:run >> /dev/null 2>&1
```

| Tâche | Fréquence |
|-------|-----------|
| `logs:cleanup` | Quotidien 00:00 |
| `quests:generate` | Quotidien 00:05 |
| `shop:refresh` | Toutes les 6h |
| `world-boss:spawn` | Tous les 3 jours à 12:00 |
| `world-boss:auto-attack` | Toutes les 2h |
| `zones:generate` | Lundi 02:00 |

---

## Base de données (51 migrations)

| Groupe | Tables |
|--------|--------|
| Config | `game_settings` |
| Auth | `users`, `personal_access_tokens` |
| Héros | `races`, `classes`, `traits`, `heroes`, `hero_buffs`, `talents`, `hero_talents` |
| Inventaire | `items`, `item_effects`, `item_templates`, `materials`, `user_materials` |
| Consommables | `consumables`, `user_consumables` |
| Monde | `zones`, `monsters`, `monster_skills`, `elite_prefixes`, `encounter_groups`, `element_chart` |
| Quêtes | `quests`, `quest_steps`, `user_quests`, `user_daily_quests`, `user_zone_progress` |
| Exploration | `user_exploration`, `idle_event_log` |
| Boutiques | `shop_inventories` |
| Donjons | `dungeons` |
| Boss mondial | `world_bosses`, `boss_contributions` |
| Crafting | `recipes` |
| Taverne | `tavern_recruits` |
| Événements | `seasonal_events` |
| IA | `narrator_cache`, `ai_generation_log`, `tavern_music` |
| Logs | `combat_log`, `economy_log` |

**Conventions :**
- IDs : `BIGINT UNSIGNED AUTO_INCREMENT`
- Aucun `FLOAT`/`DOUBLE` — tout en `INT` (percentages `× bonus / 100`)
- Timestamps logs : `occurred_at` (pas `created_at`)
- FK `ON DELETE CASCADE` sauf mention contraire

---

## Intégration Gemini AI

Le jeu fonctionne **entièrement sans clé API** grâce aux fallbacks statiques.

```bash
# Activer l'IA
php artisan tinker --execute="
  DB::table('game_settings')
    ->where('setting_key','AI_ENABLED')
    ->update(['setting_value' => 1]);
"
```

Variables `.env` requises :
```env
GEMINI_API_KEY=AIzaSy...
```

| Fonctionnalité | Modèle | Fallback |
|----------------|--------|---------|
| Narration & texte | `gemini-2.0-flash` | Templates statiques |
| Noms/descriptions loot Rare+ | `gemini-2.0-flash` | Templates génériques |
| Illustrations de loot | `imagen-3.0-generate-002` | Placeholders par slot/rareté |
| Quêtes quotidiennes | `gemini-2.0-flash` | Quêtes de zone |
| Boss mondial (texte) | `gemini-2.0-flash` | Template statique |
| Héros légendaires (biographie) | `gemini-2.0-flash` | 6 épithètes prédéfinis |
| Zones procédurales | `gemini-2.0-flash` | 6 thèmes prédéfinis |

Tous les appels sont loggés dans `ai_generation_log`. Le budget journalier est contrôlé via `AI_DAILY_BUDGET_LIMIT` dans `game_settings`.

---

## Règles techniques critiques

**Integer-only.** Aucun float nulle part. Division toujours entière (`intdiv`). Les pourcentages s'appliquent comme `valeur × bonus / 100`.

**Toutes les constantes via `SettingsService`.** Ne jamais hardcoder une valeur de gameplay. Toutes les ~120 constantes sont dans `game_settings` et accessibles via `$this->settings->get('CLÉ', défaut)`.

**Fallbacks Gemini obligatoires.** Chaque méthode de `GeminiService` retourne un résultat statique valide si l'IA est désactivée ou le budget atteint.

**Calcul offline ≠ simulation tour par tour.** `IdleService` utilise un ratio de puissance simplifié. Cap absolu à 12 heures.

**Logs purgés régulièrement.** `combat_log`, `economy_log` et `idle_event_log` sont nettoyés par `logs:cleanup`. Sans purge, la base de données croît sans limite.

**`GameClass` et `Trait_`.** `Class` et `Trait` sont des mots réservés en PHP — les modèles s'appellent `GameClass` et `Trait_`.

---

## Documents de design

| Fichier | Contenu |
|---------|---------|
| `GDD.md` | Vision complète, boucle de gameplay, systèmes |
| `COMBAT_SYSTEM.md` | Formules exactes, stats, simulation offline |
| `TALENT_TREES.md` | 168 talents détaillés par classe et branche |
| `TRAITS_SYSTEM.md` | 10 traits, déclenchement, synergies |
| `LOOT_CRAFTING.md` | Raretés, effets, fusion, enchantements |
| `BESTIARY.md` | Monstres, boss, système élémentaire |
| `QUESTS_EFFECTS.md` | Types de quêtes, buffs/debuffs, validation Gemini |
| `ECONOMY.md` | Or, matériaux, boutiques, équilibrage |
| `DATABASE.md` | Schéma SQL complet, index, diagrammes |
| `INSTALL_CPANEL.md` | Guide de déploiement sur hébergement cPanel |

---

## Tests

```
255 tests — 618 assertions — 1 skipped
```

```bash
php artisan test
```

Les tests utilisent `RefreshDatabase` (SQLite en mémoire) et ne nécessitent aucune configuration externe.
