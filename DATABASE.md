# 🗄️ Structure de Base de Données — MariaDB

## 1. Vue d'ensemble

### Groupes de tables

| Groupe | Tables | Description |
|--------|--------|-------------|
| Configuration | 1 | Constantes paramétrables du jeu |
| Utilisateurs | 2 | Comptes et sessions |
| Héros | 5 | Personnages, stats, talents, effets |
| Inventaire & Équipement | 4 | Objets, matériaux, équipement |
| Monde & Zones | 5 | Zones, monstres, donjons |
| Quêtes | 5 | Quêtes, étapes, progression |
| Narration & IA | 3 | Narrateur, cache IA, musique |
| Social & Événements | 4 | Boss mondiaux, réputation, PNJ |
| Crafting | 3 | Recettes, enchantements |
| Logs | 2 | Combat, économie |
| **Total** | **34 tables** | |

### Conventions

- Tous les IDs sont `BIGINT UNSIGNED AUTO_INCREMENT`
- Toutes les dates sont `TIMESTAMP DEFAULT CURRENT_TIMESTAMP`
- Pas de `FLOAT` ni `DOUBLE` — tout en `INT` ou `SMALLINT`
- Clés étrangères avec `ON DELETE CASCADE` sauf mention contraire
- Nommage : `snake_case`, préfixe du groupe quand utile
- Les ENUMs sont utilisés pour les types fixes (raretés, éléments, etc.)

---

## 2. Configuration

### `game_settings`

Toutes les constantes paramétrables du jeu.

```sql
CREATE TABLE game_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value INT NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

> Contient toutes les constantes des 6 documents : COMBAT_MAX_TURNS, LOOT_RARITY_WTF, TRAIT_COUARD_CHANCE, etc. ~120 entrées.

---

## 3. Utilisateurs

### `users`

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    gold INT UNSIGNED NOT NULL DEFAULT 0,
    level INT UNSIGNED NOT NULL DEFAULT 1,
    xp INT UNSIGNED NOT NULL DEFAULT 0,
    xp_to_next_level INT UNSIGNED NOT NULL DEFAULT 100,
    current_zone_id BIGINT UNSIGNED DEFAULT NULL,
    inventory_slots INT UNSIGNED NOT NULL DEFAULT 100,
    last_online_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_idle_calc_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    narrator_frequency ENUM('bavard','normal','discret','muet') NOT NULL DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_email (email),
    INDEX idx_users_current_zone (current_zone_id)
) ENGINE=InnoDB;
```

### `personal_access_tokens` (Laravel Sanctum)

```sql
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT DEFAULT NULL,
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tokens_tokenable (tokenable_type, tokenable_id)
) ENGINE=InnoDB;
```

---

## 4. Héros

### `races`

```sql
CREATE TABLE races (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    base_hp INT NOT NULL,
    base_atq INT NOT NULL,
    base_def INT NOT NULL,
    base_vit INT NOT NULL,
    base_cha INT NOT NULL,
    base_int INT NOT NULL,
    passive_bonus_description VARCHAR(255) NOT NULL,
    passive_bonus_key VARCHAR(50) NOT NULL,
    passive_bonus_value INT NOT NULL
) ENGINE=InnoDB;
```

### `classes`

```sql
CREATE TABLE classes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    role VARCHAR(30) NOT NULL,
    mod_hp INT NOT NULL DEFAULT 0,
    mod_atq INT NOT NULL DEFAULT 0,
    mod_def INT NOT NULL DEFAULT 0,
    mod_vit INT NOT NULL DEFAULT 0,
    mod_cha INT NOT NULL DEFAULT 0,
    mod_int INT NOT NULL DEFAULT 0,
    primary_stats JSON NOT NULL COMMENT '["hp","atq","def"] — stats qui reçoivent LEVEL_SCALING_FACTOR',
    weapon_types JSON NOT NULL COMMENT '["epee","hache","masse"]',
    armor_types JSON NOT NULL COMMENT '["lourde","moyenne"]'
) ENGINE=InnoDB;
```

### `traits`

```sql
CREATE TABLE traits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NOT NULL,
    flavor_text VARCHAR(255) NOT NULL,
    trigger_moment ENUM('turn_start','after_attack','after_combat','dungeon_entry','permanent','on_target_low_hp') NOT NULL,
    base_chance INT NOT NULL COMMENT '% de déclenchement de base',
    chance_level_26 INT NOT NULL,
    chance_level_51 INT NOT NULL,
    chance_level_76 INT NOT NULL,
    effect_data JSON NOT NULL COMMENT 'Données spécifiques au trait (durée sommeil, % dégâts feu, etc.)',
    scaling_data JSON DEFAULT NULL COMMENT 'Scaling avec le niveau (ex: dégâts pyromane qui augmentent)',
    out_of_combat_effect VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;
```

### `heroes`

```sql
CREATE TABLE heroes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    race_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    trait_id BIGINT UNSIGNED NOT NULL,
    level INT UNSIGNED NOT NULL DEFAULT 1,
    xp INT UNSIGNED NOT NULL DEFAULT 0,
    xp_to_next_level INT UNSIGNED NOT NULL DEFAULT 100,
    current_hp INT NOT NULL,
    max_hp INT NOT NULL,
    base_atq INT NOT NULL,
    base_def INT NOT NULL,
    base_vit INT NOT NULL,
    base_cha INT NOT NULL,
    base_int INT NOT NULL,
    talent_points_available INT UNSIGNED NOT NULL DEFAULT 0,
    talent_points_spent INT UNSIGNED NOT NULL DEFAULT 0,
    talent_reset_count INT UNSIGNED NOT NULL DEFAULT 0,
    slot_index TINYINT UNSIGNED NOT NULL COMMENT '1-5, position dans l équipe',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_absent_until TIMESTAMP NULL DEFAULT NULL COMMENT 'Héros temporairement absent (quête)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (race_id) REFERENCES races(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (trait_id) REFERENCES traits(id),
    INDEX idx_heroes_user (user_id),
    INDEX idx_heroes_active (user_id, is_active)
) ENGINE=InnoDB;
```

### `hero_talents`

```sql
CREATE TABLE hero_talents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_id BIGINT UNSIGNED NOT NULL,
    talent_id BIGINT UNSIGNED NOT NULL,
    unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hero_id) REFERENCES heroes(id) ON DELETE CASCADE,
    FOREIGN KEY (talent_id) REFERENCES talents(id),
    UNIQUE KEY uk_hero_talent (hero_id, talent_id),
    INDEX idx_hero_talents_hero (hero_id)
) ENGINE=InnoDB;
```

### `talents`

```sql
CREATE TABLE talents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    branch ENUM('A','B','C') NOT NULL COMMENT 'A=spé1, B=spé2, C=défaut',
    branch_name VARCHAR(50) NOT NULL,
    tier TINYINT UNSIGNED NOT NULL COMMENT '1, 2 ou 3',
    position TINYINT UNSIGNED NOT NULL COMMENT '1-7 dans la branche',
    name VARCHAR(50) NOT NULL,
    type ENUM('passif','actif','reactif') NOT NULL,
    cost TINYINT UNSIGNED NOT NULL COMMENT '1-3 points',
    tier_points_required TINYINT UNSIGNED NOT NULL COMMENT 'Points investis dans la branche pour débloquer ce palier',
    description TEXT NOT NULL,
    effect_data JSON NOT NULL COMMENT 'Données mécaniques : stats, %, durée, cooldown, etc.',
    cooldown INT UNSIGNED DEFAULT NULL COMMENT 'En tours, pour les talents actifs',
    is_capstone TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    INDEX idx_talents_class_branch (class_id, branch)
) ENGINE=InnoDB;
```

### `hero_buffs`

Buffs et debuffs temporaires actifs sur les héros.

```sql
CREATE TABLE hero_buffs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hero_id BIGINT UNSIGNED NOT NULL,
    buff_id VARCHAR(10) NOT NULL COMMENT 'ID du buff/debuff (B01, D01, etc.)',
    name VARCHAR(50) NOT NULL,
    is_debuff TINYINT(1) NOT NULL DEFAULT 0,
    effect_data JSON NOT NULL COMMENT 'Stats modifiées et valeurs',
    remaining_combats INT UNSIGNED NOT NULL,
    source VARCHAR(50) DEFAULT NULL COMMENT 'quête, objet, événement...',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hero_id) REFERENCES heroes(id) ON DELETE CASCADE,
    INDEX idx_hero_buffs_hero (hero_id),
    INDEX idx_hero_buffs_remaining (remaining_combats)
) ENGINE=InnoDB;
```

---

## 5. Inventaire & Équipement

### `items`

```sql
CREATE TABLE items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    template_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'NULL si généré par IA',
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    slot ENUM('arme','armure','casque','bottes','accessoire','truc_bizarre') NOT NULL,
    rarity ENUM('commun','peu_commun','rare','epique','legendaire','wtf') NOT NULL,
    item_level INT UNSIGNED NOT NULL,
    stat_hp INT NOT NULL DEFAULT 0,
    stat_atq INT NOT NULL DEFAULT 0,
    stat_def INT NOT NULL DEFAULT 0,
    stat_vit INT NOT NULL DEFAULT 0,
    stat_cha INT NOT NULL DEFAULT 0,
    stat_int INT NOT NULL DEFAULT 0,
    element ENUM('physique','feu','glace','foudre','poison','sacre_ombre') DEFAULT 'physique',
    durability_current INT UNSIGNED NOT NULL DEFAULT 100,
    durability_max INT UNSIGNED NOT NULL DEFAULT 100,
    sell_value INT UNSIGNED NOT NULL DEFAULT 0,
    equipped_by_hero_id BIGINT UNSIGNED DEFAULT NULL,
    ai_generated TINYINT(1) NOT NULL DEFAULT 0,
    image_path VARCHAR(255) DEFAULT NULL,
    boss_origin_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'Si objet de boss mondial',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (equipped_by_hero_id) REFERENCES heroes(id) ON DELETE SET NULL,
    INDEX idx_items_user (user_id),
    INDEX idx_items_equipped (equipped_by_hero_id),
    INDEX idx_items_rarity (rarity)
) ENGINE=InnoDB;
```

### `item_effects`

Effets spéciaux sur les objets (1-2 par objet Rare+).

```sql
CREATE TABLE item_effects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id BIGINT UNSIGNED NOT NULL,
    effect_id VARCHAR(10) NOT NULL COMMENT 'R01, E01, L01, W01, etc.',
    name VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    effect_data JSON NOT NULL COMMENT 'Mécaniques : %, triggers, durées',
    is_enchantment TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 si ajouté par enchantement',
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    INDEX idx_item_effects_item (item_id)
) ENGINE=InnoDB;
```

### `item_templates`

Objets prédéfinis (Commun/Peu commun) par zone.

```sql
CREATE TABLE item_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    zone_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    slot ENUM('arme','armure','casque','bottes','accessoire','truc_bizarre') NOT NULL,
    rarity ENUM('commun','peu_commun') NOT NULL,
    base_stat_primary VARCHAR(10) NOT NULL COMMENT 'atq, def, vit, etc.',
    base_stat_secondary VARCHAR(10) DEFAULT NULL,
    weapon_type VARCHAR(30) DEFAULT NULL,
    armor_type VARCHAR(30) DEFAULT NULL,
    FOREIGN KEY (zone_id) REFERENCES zones(id),
    INDEX idx_item_templates_zone (zone_id)
) ENGINE=InnoDB;
```

### `user_materials`

```sql
CREATE TABLE user_materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    material_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id),
    UNIQUE KEY uk_user_material (user_id, material_id),
    INDEX idx_user_materials_user (user_id)
) ENGINE=InnoDB;
```

### `materials`

```sql
CREATE TABLE materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    rarity ENUM('commun','peu_commun','rare','tres_rare','ultra_rare','special') NOT NULL,
    zone_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'NULL = générique ou cross-zone',
    drop_chance INT UNSIGNED DEFAULT NULL COMMENT '% de chance par combat dans la zone',
    source_description VARCHAR(255) DEFAULT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL,
    INDEX idx_materials_zone (zone_id)
) ENGINE=InnoDB;
```

---

## 6. Monde & Zones

### `zones`

```sql
CREATE TABLE zones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    level_min INT UNSIGNED NOT NULL,
    level_max INT UNSIGNED NOT NULL,
    dominant_element ENUM('physique','feu','glace','foudre','poison','sacre_ombre','mixte') NOT NULL,
    is_magical TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Déclenche le trait Allergique',
    unlock_requirement VARCHAR(255) DEFAULT NULL COMMENT 'Quête ou niveau requis',
    order_index INT UNSIGNED NOT NULL COMMENT 'Ordre séquentiel des zones',
    ai_generated TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Zone 9+ générée par IA'
) ENGINE=InnoDB;
```

### `monsters`

```sql
CREATE TABLE monsters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    zone_id BIGINT UNSIGNED NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL,
    description TEXT DEFAULT NULL,
    element ENUM('physique','feu','glace','foudre','poison','sacre_ombre') NOT NULL,
    monster_type ENUM('normal','mini_boss','boss') NOT NULL DEFAULT 'normal',
    base_hp INT NOT NULL,
    base_atq INT NOT NULL,
    base_def INT NOT NULL,
    base_vit INT NOT NULL,
    behavior VARCHAR(255) DEFAULT NULL COMMENT 'Description du comportement IA',
    behavior_data JSON DEFAULT NULL COMMENT 'Cibles prioritaires, conditions',
    phase2_hp_threshold INT DEFAULT NULL COMMENT '% PV pour passer en phase 2 (boss)',
    phase2_data JSON DEFAULT NULL COMMENT 'Changements de stats/skills en phase 2',
    loot_description TEXT DEFAULT NULL COMMENT 'Loot garanti pour les boss',
    image_path VARCHAR(255) DEFAULT NULL,
    ai_generated TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (zone_id) REFERENCES zones(id),
    INDEX idx_monsters_zone (zone_id),
    INDEX idx_monsters_type (monster_type)
) ENGINE=InnoDB;
```

### `monster_skills`

```sql
CREATE TABLE monster_skills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    monster_id BIGINT UNSIGNED NOT NULL,
    skill_id VARCHAR(10) NOT NULL COMMENT 'MO01, MD01, etc. ou custom',
    name VARCHAR(50) NOT NULL,
    type ENUM('offensive','defensive','support') NOT NULL,
    description TEXT NOT NULL,
    effect_data JSON NOT NULL COMMENT 'Dégâts %, cibles, effets de statut',
    cooldown INT UNSIGNED NOT NULL DEFAULT 3,
    phase TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0=toutes phases, 1=phase1 only, 2=phase2 only',
    FOREIGN KEY (monster_id) REFERENCES monsters(id) ON DELETE CASCADE,
    INDEX idx_monster_skills_monster (monster_id)
) ENGINE=InnoDB;
```

### `elite_prefixes`

```sql
CREATE TABLE elite_prefixes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(30) NOT NULL,
    stat_hp_mult INT NOT NULL DEFAULT 100 COMMENT '% multiplicateur',
    stat_atq_mult INT NOT NULL DEFAULT 100,
    stat_def_mult INT NOT NULL DEFAULT 100,
    stat_vit_mult INT NOT NULL DEFAULT 100,
    special_effect VARCHAR(255) NOT NULL,
    effect_data JSON NOT NULL
) ENGINE=InnoDB;
```

### `encounter_groups`

Définit les compositions de groupes ennemis par zone et plage de niveaux.

```sql
CREATE TABLE encounter_groups (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    zone_id BIGINT UNSIGNED NOT NULL,
    level_min INT UNSIGNED NOT NULL,
    level_max INT UNSIGNED NOT NULL,
    monster_ids JSON NOT NULL COMMENT '[monster_id, monster_id, ...]',
    weight INT UNSIGNED NOT NULL DEFAULT 100 COMMENT 'Pondération de sélection',
    FOREIGN KEY (zone_id) REFERENCES zones(id),
    INDEX idx_encounter_zone_level (zone_id, level_min, level_max)
) ENGINE=InnoDB;
```

---

## 7. Quêtes

### `quests`

```sql
CREATE TABLE quests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    zone_id BIGINT UNSIGNED DEFAULT NULL,
    type ENUM('zone','quotidienne','evenementielle','wtf') NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    order_index INT UNSIGNED DEFAULT NULL COMMENT 'Ordre séquentiel pour les quêtes de zone',
    level_min INT UNSIGNED DEFAULT NULL,
    level_max INT UNSIGNED DEFAULT NULL,
    steps_count INT UNSIGNED NOT NULL,
    reward_xp_formula VARCHAR(100) DEFAULT NULL COMMENT 'Ex: zone_level*50*order_index',
    reward_gold_formula VARCHAR(100) DEFAULT NULL,
    reward_reputation INT UNSIGNED DEFAULT 0,
    reward_loot_rarity_min ENUM('commun','peu_commun','rare','epique','legendaire','wtf') DEFAULT NULL,
    reward_item_template_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'Loot fixe de quête',
    reward_recipe_id BIGINT UNSIGNED DEFAULT NULL,
    reward_world_effect_id VARCHAR(10) DEFAULT NULL COMMENT 'M01, M02, etc.',
    ai_generated TINYINT(1) NOT NULL DEFAULT 0,
    is_template TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 pour les quêtes dans le pool IA',
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL,
    INDEX idx_quests_zone_type (zone_id, type),
    INDEX idx_quests_type (type)
) ENGINE=InnoDB;
```

### `quest_steps`

```sql
CREATE TABLE quest_steps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quest_id BIGINT UNSIGNED NOT NULL,
    step_order INT UNSIGNED NOT NULL,
    narration TEXT NOT NULL,
    narrator_comment TEXT DEFAULT NULL,
    surprise_eligible TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (quest_id) REFERENCES quests(id) ON DELETE CASCADE,
    INDEX idx_quest_steps_quest (quest_id)
) ENGINE=InnoDB;
```

### `quest_choices`

```sql
CREATE TABLE quest_choices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    step_id BIGINT UNSIGNED NOT NULL,
    choice_label CHAR(1) NOT NULL COMMENT 'A, B, C...',
    text TEXT NOT NULL,
    test_type ENUM('none','stat','combat') DEFAULT 'none',
    test_stat VARCHAR(10) DEFAULT NULL COMMENT 'ATQ, DEF, CHA, etc.',
    test_difficulty INT UNSIGNED DEFAULT NULL,
    test_trait_bonuses JSON DEFAULT NULL COMMENT '{"Mythomane": 20, "Pacifiste": -10}',
    test_enemy_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'Pour les tests de combat',
    success_next_step_id BIGINT UNSIGNED DEFAULT NULL,
    success_effects JSON DEFAULT NULL COMMENT '[{"type":"buff","id":"B06","target":"party"}, ...]',
    success_narration TEXT DEFAULT NULL,
    failure_next_step_id BIGINT UNSIGNED DEFAULT NULL,
    failure_effects JSON DEFAULT NULL,
    failure_narration TEXT DEFAULT NULL,
    voice ENUM('heroique','maligne','comique','prudente') DEFAULT NULL COMMENT 'Voie narrative de ce choix',
    gold_cost INT DEFAULT NULL COMMENT 'Or requis/gagné par ce choix',
    FOREIGN KEY (step_id) REFERENCES quest_steps(id) ON DELETE CASCADE,
    FOREIGN KEY (test_enemy_id) REFERENCES monsters(id) ON DELETE SET NULL,
    INDEX idx_quest_choices_step (step_id)
) ENGINE=InnoDB;
```

### `user_quest_progress`

```sql
CREATE TABLE user_quest_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    quest_id BIGINT UNSIGNED NOT NULL,
    current_step_id BIGINT UNSIGNED DEFAULT NULL,
    status ENUM('available','in_progress','completed','failed') NOT NULL DEFAULT 'available',
    voice_heroique INT UNSIGNED NOT NULL DEFAULT 0,
    voice_maligne INT UNSIGNED NOT NULL DEFAULT 0,
    voice_comique INT UNSIGNED NOT NULL DEFAULT 0,
    voice_prudente INT UNSIGNED NOT NULL DEFAULT 0,
    started_at TIMESTAMP NULL DEFAULT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quest_id) REFERENCES quests(id),
    FOREIGN KEY (current_step_id) REFERENCES quest_steps(id) ON DELETE SET NULL,
    UNIQUE KEY uk_user_quest (user_id, quest_id),
    INDEX idx_user_quest_status (user_id, status)
) ENGINE=InnoDB;
```

### `user_daily_quests`

```sql
CREATE TABLE user_daily_quests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    quest_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    status ENUM('available','in_progress','completed') NOT NULL DEFAULT 'available',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quest_id) REFERENCES quests(id),
    INDEX idx_daily_quests_user_date (user_id, date)
) ENGINE=InnoDB;
```

---

## 8. Narration & IA

### `narrator_cache`

Cache les commentaires générés par le Narrateur pour réutilisation.

```sql
CREATE TABLE narrator_cache (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL COMMENT 'combat_win, combat_lose, loot_found, hero_death, etc.',
    context_hash VARCHAR(64) NOT NULL COMMENT 'Hash du contexte (zone+héros+situation)',
    text TEXT NOT NULL,
    usage_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_narrator_event_context (event_type, context_hash)
) ENGINE=InnoDB;
```

### `ai_generation_log`

Suivi des appels API Gemini pour le budget.

```sql
CREATE TABLE ai_generation_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('narration','loot_text','loot_image','quest','music','boss','zone') NOT NULL,
    prompt_summary VARCHAR(255) DEFAULT NULL,
    tokens_used INT UNSIGNED DEFAULT NULL,
    cost_estimate INT UNSIGNED DEFAULT NULL COMMENT 'En micro-centimes',
    success TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ai_log_type_date (type, created_at)
) ENGINE=InnoDB;
```

### `tavern_music`

```sql
CREATE TABLE tavern_music (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    style VARCHAR(50) NOT NULL COMMENT 'victoire_epique, defaite, exploration, etc.',
    prompt_used TEXT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    duration_seconds INT UNSIGNED DEFAULT NULL,
    play_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_music_style (style)
) ENGINE=InnoDB;
```

---

## 9. Social & Événements

### `world_bosses`

```sql
CREATE TABLE world_bosses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    element ENUM('physique','feu','glace','foudre','poison','sacre_ombre','mixte') NOT NULL,
    base_hp_per_player INT UNSIGNED NOT NULL,
    base_atq INT UNSIGNED NOT NULL,
    base_def INT UNSIGNED NOT NULL,
    base_vit INT UNSIGNED NOT NULL,
    current_hp BIGINT NOT NULL COMMENT 'PV restants (peut être très grand)',
    max_hp BIGINT NOT NULL,
    mechanics JSON NOT NULL COMMENT 'Tableau des mécaniques spéciales',
    status ENUM('upcoming','active','defeated') NOT NULL DEFAULT 'upcoming',
    starts_at TIMESTAMP NOT NULL,
    defeated_at TIMESTAMP NULL DEFAULT NULL,
    reward_items JSON DEFAULT NULL COMMENT 'Objets thématiques générés pour le top 10%',
    ai_generated TINYINT(1) NOT NULL DEFAULT 0,
    image_path VARCHAR(255) DEFAULT NULL,
    INDEX idx_world_boss_status (status)
) ENGINE=InnoDB;
```

### `world_boss_contributions`

```sql
CREATE TABLE world_boss_contributions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    world_boss_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    total_damage BIGINT UNSIGNED NOT NULL DEFAULT 0,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    last_attempt_at TIMESTAMP NULL DEFAULT NULL,
    rank_position INT UNSIGNED DEFAULT NULL COMMENT 'Calculé à la défaite du boss',
    reward_claimed TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (world_boss_id) REFERENCES world_bosses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_boss_user (world_boss_id, user_id),
    INDEX idx_contributions_damage (world_boss_id, total_damage DESC)
) ENGINE=InnoDB;
```

### `user_reputations`

```sql
CREATE TABLE user_reputations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    zone_id BIGINT UNSIGNED NOT NULL,
    reputation INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(id),
    UNIQUE KEY uk_user_zone_rep (user_id, zone_id),
    INDEX idx_reputation_user (user_id)
) ENGINE=InnoDB;
```

### `user_npc_relations`

```sql
CREATE TABLE user_npc_relations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    npc_id BIGINT UNSIGNED NOT NULL,
    relation_score INT UNSIGNED NOT NULL DEFAULT 0,
    gifts_today INT UNSIGNED NOT NULL DEFAULT 0,
    last_gift_date DATE DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (npc_id) REFERENCES npcs(id),
    UNIQUE KEY uk_user_npc (user_id, npc_id),
    INDEX idx_npc_rel_user (user_id)
) ENGINE=InnoDB;
```

### `npcs`

```sql
CREATE TABLE npcs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL,
    zone_id BIGINT UNSIGNED DEFAULT NULL,
    description TEXT DEFAULT NULL,
    initial_relation INT UNSIGNED NOT NULL DEFAULT 0,
    best_friend_effect TEXT DEFAULT NULL COMMENT 'Description de l effet à relation 100',
    best_friend_effect_data JSON DEFAULT NULL,
    INDEX idx_npcs_zone (zone_id)
) ENGINE=InnoDB;
```

---

## 10. Crafting

### `recipes`

```sql
CREATE TABLE recipes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    type ENUM('consommable','equipement','enchantement') NOT NULL,
    zone_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'NULL = recette générique',
    gold_cost INT UNSIGNED NOT NULL DEFAULT 0,
    result_item_template_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'Pour les recettes d équipement prédéfini',
    result_data JSON NOT NULL COMMENT 'Données du résultat : type, rareté, stats, effets',
    is_discoverable TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = disponible dès le début',
    discovery_source VARCHAR(100) DEFAULT NULL COMMENT 'quête, craft, zone...',
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL,
    INDEX idx_recipes_zone (zone_id),
    INDEX idx_recipes_type (type)
) ENGINE=InnoDB;
```

### `recipe_ingredients`

```sql
CREATE TABLE recipe_ingredients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id BIGINT UNSIGNED NOT NULL,
    material_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(id),
    INDEX idx_recipe_ingredients_recipe (recipe_id)
) ENGINE=InnoDB;
```

### `user_recipes`

Recettes découvertes par le joueur.

```sql
CREATE TABLE user_recipes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    recipe_id BIGINT UNSIGNED NOT NULL,
    discovered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id),
    UNIQUE KEY uk_user_recipe (user_id, recipe_id),
    INDEX idx_user_recipes_user (user_id)
) ENGINE=InnoDB;
```

---

## 11. Boutiques

### `shop_inventories`

Stock actuel des boutiques (régénéré périodiquement).

```sql
CREATE TABLE shop_inventories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    zone_id BIGINT UNSIGNED NOT NULL,
    shop_type ENUM('zone','taverne','gerard','pnj','evenement') NOT NULL,
    item_data JSON NOT NULL COMMENT 'Objet généré : nom, stats, rareté, prix',
    price INT UNSIGNED NOT NULL,
    is_sold TINYINT(1) NOT NULL DEFAULT 0,
    refreshed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (zone_id) REFERENCES zones(id),
    INDEX idx_shop_zone_type (zone_id, shop_type),
    INDEX idx_shop_expires (expires_at)
) ENGINE=InnoDB;
```

---

## 12. Exploration & Idle

### `user_exploration`

État de l'exploration idle du joueur.

```sql
CREATE TABLE user_exploration (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    zone_id BIGINT UNSIGNED NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_event_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    combats_completed INT UNSIGNED NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(id),
    INDEX idx_exploration_user (user_id, is_active)
) ENGINE=InnoDB;
```

### `idle_event_log`

Événements idle survenus pendant l'absence du joueur.

```sql
CREATE TABLE idle_event_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    zone_id BIGINT UNSIGNED NOT NULL,
    event_type VARCHAR(10) NOT NULL COMMENT 'I01, I02, etc.',
    event_name VARCHAR(80) NOT NULL,
    event_description TEXT NOT NULL,
    effect_data JSON DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_idle_log_user (user_id, is_read),
    INDEX idx_idle_log_date (occurred_at)
) ENGINE=InnoDB;
```

### `user_zone_progress`

Progression du joueur dans chaque zone.

```sql
CREATE TABLE user_zone_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    zone_id BIGINT UNSIGNED NOT NULL,
    is_unlocked TINYINT(1) NOT NULL DEFAULT 0,
    is_completed TINYINT(1) NOT NULL DEFAULT 0,
    boss_defeated TINYINT(1) NOT NULL DEFAULT 0,
    miniboss_defeated TINYINT(1) NOT NULL DEFAULT 0,
    quests_completed INT UNSIGNED NOT NULL DEFAULT 0,
    total_kills INT UNSIGNED NOT NULL DEFAULT 0,
    world_effects JSON DEFAULT NULL COMMENT 'Effets monde actifs (M01, M03, etc.)',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(id),
    UNIQUE KEY uk_user_zone (user_id, zone_id),
    INDEX idx_zone_progress_user (user_id)
) ENGINE=InnoDB;
```

---

## 13. Donjons

### `dungeon_instances`

```sql
CREATE TABLE dungeon_instances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    zone_id BIGINT UNSIGNED NOT NULL,
    rooms_total INT UNSIGNED NOT NULL,
    rooms_completed INT UNSIGNED NOT NULL DEFAULT 0,
    difficulty_multiplier INT UNSIGNED NOT NULL DEFAULT 100 COMMENT '% choisi par le joueur',
    room_data JSON NOT NULL COMMENT 'Layout des salles : monstres, pièges, coffres',
    status ENUM('in_progress','completed','failed','abandoned') NOT NULL DEFAULT 'in_progress',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    next_available_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Timer pour le prochain donjon',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(id),
    INDEX idx_dungeon_user (user_id, status)
) ENGINE=InnoDB;
```

---

## 14. Logs

### `combat_log`

```sql
CREATE TABLE combat_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    zone_id BIGINT UNSIGNED NOT NULL,
    combat_type ENUM('idle','dungeon','quest','world_boss') NOT NULL,
    enemy_data JSON NOT NULL COMMENT 'Monstres affrontés, élites, etc.',
    result ENUM('victory','defeat','draw') NOT NULL,
    turns INT UNSIGNED NOT NULL,
    xp_gained INT UNSIGNED NOT NULL DEFAULT 0,
    gold_gained INT UNSIGNED NOT NULL DEFAULT 0,
    loot_gained JSON DEFAULT NULL COMMENT 'IDs des objets obtenus',
    materials_gained JSON DEFAULT NULL,
    trait_triggers JSON DEFAULT NULL COMMENT 'Traits déclenchés pendant le combat',
    narrator_comment TEXT DEFAULT NULL,
    occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_combat_log_user_date (user_id, occurred_at),
    INDEX idx_combat_log_type (combat_type)
) ENGINE=InnoDB;
```

### `economy_log`

```sql
CREATE TABLE economy_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('earn','spend') NOT NULL,
    source VARCHAR(50) NOT NULL COMMENT 'combat, quest, shop, craft, repair, talent_reset, etc.',
    gold_amount INT NOT NULL COMMENT 'Positif = gagné, négatif = dépensé',
    balance_after INT UNSIGNED NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    occurred_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_economy_user_date (user_id, occurred_at),
    INDEX idx_economy_source (source)
) ENGINE=InnoDB;
```

---

## 15. Surprise Events

### `surprise_events`

Pool d'événements surprise disponibles.

```sql
CREATE TABLE surprise_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id VARCHAR(10) NOT NULL UNIQUE COMMENT 'S01, S02, I01, etc.',
    name VARCHAR(80) NOT NULL,
    category ENUM('positif','negatif','mixte','narratif','comique','choix','rare') NOT NULL,
    context ENUM('quest','idle') NOT NULL,
    description TEXT NOT NULL,
    effect_data JSON NOT NULL,
    chance_weight INT UNSIGNED NOT NULL DEFAULT 100 COMMENT 'Pondération de sélection',
    zone_id BIGINT UNSIGNED DEFAULT NULL COMMENT 'NULL = toutes zones',
    FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE SET NULL
) ENGINE=InnoDB;
```

---

## 16. Éléments

### `element_chart`

Table de multiplicateurs élémentaires.

```sql
CREATE TABLE element_chart (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    attacker_element ENUM('physique','feu','glace','foudre','poison','sacre_ombre') NOT NULL,
    defender_element ENUM('physique','feu','glace','foudre','poison','sacre_ombre') NOT NULL,
    damage_multiplier INT UNSIGNED NOT NULL DEFAULT 100 COMMENT '100=neutre, 150=super efficace, 50=résistant',
    UNIQUE KEY uk_element_matchup (attacker_element, defender_element)
) ENGINE=InnoDB;
```

---

## 17. Diagramme des relations principales

```
users
  ├── heroes (1:N)
  │     ├── hero_talents (1:N) → talents → classes
  │     ├── hero_buffs (1:N)
  │     └── items [equipped] (1:N)
  │           └── item_effects (1:N)
  ├── items [inventory] (1:N)
  ├── user_materials (1:N) → materials
  ├── user_recipes (1:N) → recipes → recipe_ingredients → materials
  ├── user_quest_progress (1:N) → quests → quest_steps → quest_choices
  ├── user_daily_quests (1:N) → quests
  ├── user_reputations (1:N) → zones
  ├── user_npc_relations (1:N) → npcs
  ├── user_exploration (1:1 active) → zones
  ├── user_zone_progress (1:N) → zones
  ├── dungeon_instances (1:N) → zones
  ├── world_boss_contributions (1:N) → world_bosses
  ├── idle_event_log (1:N)
  ├── combat_log (1:N)
  └── economy_log (1:N)

zones
  ├── monsters (1:N) → monster_skills (1:N)
  ├── item_templates (1:N)
  ├── materials (1:N)
  ├── quests (1:N)
  ├── encounter_groups (1:N)
  ├── shop_inventories (1:N)
  └── surprise_events (1:N, optionnel)

Lookup tables (pas de FK vers elles, référencées par ID/slug) :
  ├── game_settings
  ├── races
  ├── traits
  ├── elite_prefixes
  ├── element_chart
  ├── narrator_cache
  ├── tavern_music
  └── ai_generation_log
```

---

## 18. Index de performance

### Requêtes critiques et leurs index

| Requête | Tables | Index utilisé |
|---------|--------|---------------|
| Login / vérif token | personal_access_tokens | uk_token |
| Charger l'équipe du joueur | heroes | idx_heroes_active |
| Inventaire du joueur | items | idx_items_user |
| Objets équipés d'un héros | items | idx_items_equipped |
| Progression de zone | user_zone_progress | uk_user_zone |
| Quêtes disponibles | user_quest_progress | idx_user_quest_status |
| Quêtes du jour | user_daily_quests | idx_daily_quests_user_date |
| Classement boss mondial | world_boss_contributions | idx_contributions_damage |
| Événements idle non lus | idle_event_log | idx_idle_log_user |
| Historique économique | economy_log | idx_economy_user_date |
| Stock boutique actif | shop_inventories | idx_shop_expires |

---

## 19. Notes de maintenance

### Nettoyage périodique (cron Laravel)

```
Quotidien :
  - Supprimer idle_event_log > 7 jours (is_read = 1)
  - Supprimer combat_log > 30 jours
  - Supprimer economy_log > 90 jours
  - Régénérer shop_inventories expirés
  - Rafraîchir user_daily_quests

Hebdomadaire :
  - Archiver ai_generation_log > 30 jours
  - Vérifier narrator_cache (supprimer usage_count = 0 > 30 jours)
  - Recalculer les stats agrégées

Événementiel :
  - À la défaite d'un boss mondial : calculer rank_position pour tous les contributeurs
  - Au changement de zone du joueur : régénérer shop_inventories de la zone
```

### Taille estimée (1000 joueurs actifs)

| Table | Lignes estimées | Croissance |
|-------|-----------------|-----------|
| users | 1 000 | Lente |
| heroes | 5 000 | Lente |
| items | 100 000 | Moyenne |
| combat_log | 500 000/mois | Rapide (purger) |
| economy_log | 200 000/mois | Rapide (purger) |
| idle_event_log | 100 000/mois | Rapide (purger) |
| narrator_cache | 10 000 | Lente (cache) |
| Toutes autres | < 50 000 | Lente |
