<?php

namespace App\Services;

use App\Models\Dungeon;
use App\Models\Hero;
use App\Models\Monster;
use App\Models\User;
use App\Models\UserZoneProgress;
use App\Models\Zone;
use Illuminate\Support\Carbon;

class DungeonService
{
    // Room type weights (must sum to 100)
    private const ROOM_WEIGHTS = [
        'combat'   => 40,
        'treasure' => 25,
        'trap'     => 20,
        'rest'     => 15,
    ];

    // Trap damage as percentage of total team HP (scaled by zone danger = level_max)
    private const TRAP_DAMAGE_BASE_PERCENT = 10;

    // Rest heal percentage of max HP
    private const REST_HEAL_PERCENT = 25;

    // Bonus gold multiplier for dungeon completion (percentage of gold_gained added on top)
    private const COMPLETION_GOLD_BONUS_PERCENT = 50;

    public function __construct(
        private readonly SettingsService $settings,
        private readonly LootService $loot,
        private readonly NarratorService $narrator,
    ) {}

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Check if user can start a dungeon, generate rooms, persist and return status.
     */
    public function startDungeon(User $user, int $zoneId): array
    {
        // Check for existing active dungeon
        $active = Dungeon::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($active) {
            return [
                'success' => false,
                'error'   => 'Un donjon est déjà en cours. Terminez-le ou abandonnez-le d\'abord.',
            ];
        }

        // Check cooldown
        $lastDungeon = Dungeon::where('user_id', $user->id)
            ->whereNotNull('available_at')
            ->orderByDesc('id')
            ->first();

        if ($lastDungeon && $lastDungeon->available_at && $lastDungeon->available_at->isFuture()) {
            $minutesLeft = (int) now()->diffInMinutes($lastDungeon->available_at, false);
            return [
                'success'     => false,
                'error'       => 'Donjon en recharge. Revenez dans ' . $minutesLeft . ' minutes.',
                'available_at' => $lastDungeon->available_at->toIso8601String(),
            ];
        }

        // Validate zone
        $zone = Zone::find($zoneId);
        if (!$zone) {
            return ['success' => false, 'error' => 'Zone introuvable.'];
        }

        // Need at least one active hero
        $heroes = $user->activeHeroes()->with(['race', 'gameClass', 'equippedItems'])->get();
        if ($heroes->isEmpty()) {
            return ['success' => false, 'error' => 'Vous n\'avez aucun héros actif pour explorer ce donjon.'];
        }

        $rooms      = $this->generateRooms($zone);
        $totalRooms = count($rooms);

        $dungeon = Dungeon::create([
            'user_id'     => $user->id,
            'zone_id'     => $zoneId,
            'status'      => 'active',
            'current_room' => 1,
            'total_rooms' => $totalRooms,
            'rooms'       => $rooms,
            'loot_gained' => [],
            'gold_gained' => 0,
            'started_at'  => now(),
        ]);

        $narratorComment = $this->narrator->getComment('dungeon_start');

        return [
            'success'    => true,
            'dungeon_id' => $dungeon->id,
            'total_rooms' => $totalRooms,
            'current_room' => 1,
            'room_preview' => $this->buildRoomPreview($rooms[0]),
            'narrator'   => $narratorComment,
        ];
    }

    /**
     * Return current dungeon status for polling.
     */
    public function getStatus(User $user): array
    {
        $dungeon = Dungeon::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$dungeon) {
            // Return cooldown info from last dungeon
            $last = Dungeon::where('user_id', $user->id)
                ->whereNotNull('available_at')
                ->orderByDesc('id')
                ->first();

            $availableAt = $last?->available_at;
            $onCooldown  = $availableAt && $availableAt->isFuture();

            return [
                'active'      => false,
                'on_cooldown' => $onCooldown,
                'available_at' => $availableAt?->toIso8601String(),
            ];
        }

        $rooms      = $dungeon->rooms ?? [];
        $roomIndex  = $dungeon->current_room - 1;
        $currentRoom = $rooms[$roomIndex] ?? null;

        return [
            'active'       => true,
            'dungeon_id'   => $dungeon->id,
            'zone_id'      => $dungeon->zone_id,
            'status'       => $dungeon->status,
            'current_room' => $dungeon->current_room,
            'total_rooms'  => $dungeon->total_rooms,
            'room_preview' => $currentRoom ? $this->buildRoomPreview($currentRoom) : null,
            'gold_gained'  => $dungeon->gold_gained,
            'loot_count'   => count($dungeon->loot_gained ?? []),
            'started_at'   => $dungeon->started_at->toIso8601String(),
        ];
    }

    /**
     * Resolve the current room, advance to next room or mark dungeon as completed/failed.
     */
    public function enterRoom(User $user, int $dungeonId): array
    {
        $dungeon = Dungeon::where('id', $dungeonId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$dungeon) {
            return ['success' => false, 'error' => 'Donjon introuvable ou déjà terminé.'];
        }

        $rooms     = $dungeon->rooms;
        $roomIndex = $dungeon->current_room - 1;

        if (!isset($rooms[$roomIndex])) {
            return ['success' => false, 'error' => 'Salle introuvable.'];
        }

        $room = $rooms[$roomIndex];

        if (!empty($room['is_completed'])) {
            return ['success' => false, 'error' => 'Cette salle a déjà été résolue. Passez à la suivante.'];
        }

        $heroes = $user->activeHeroes()->with(['race', 'gameClass', 'equippedItems'])->get();
        if ($heroes->isEmpty()) {
            return ['success' => false, 'error' => 'Aucun héros actif.'];
        }

        $zone   = $dungeon->zone;
        $result = $this->resolveRoom($room, $heroes, $zone, $user, $dungeon);

        // Mark room completed
        $rooms[$roomIndex]['is_completed'] = true;
        $rooms[$roomIndex]['result_summary'] = $result['summary'] ?? '';

        // Accumulate rewards
        $dungeon->gold_gained = $dungeon->gold_gained + ($result['gold'] ?? 0);
        $lootGained = $dungeon->loot_gained ?? [];
        foreach ($result['loot_items'] ?? [] as $itemData) {
            $lootGained[] = $itemData;
        }
        $dungeon->loot_gained = $lootGained;
        $dungeon->rooms = $rooms;

        // Determine next state
        $isLastRoom  = ($dungeon->current_room >= $dungeon->total_rooms);
        $heroesWiped = $result['heroes_wiped'] ?? false;

        if ($heroesWiped) {
            // Arrêter l'exploration active — héros à 0 PV ne peuvent plus explorer
            $user->activeExploration()->update(['is_active' => false]);

            // Dungeon failed — set cooldown to 1 hour (shorter penalty)
            $dungeon->status       = 'failed';
            $dungeon->completed_at = now();
            $dungeon->available_at = now()->addHour();
            $dungeon->save();

            return [
                'success'      => true,
                'room_result'  => $result,
                'dungeon_over' => true,
                'outcome'      => 'failed',
                'gold_gained'  => $dungeon->gold_gained,
                'loot_count'   => count($dungeon->loot_gained ?? []),
                'available_at' => $dungeon->available_at->toIso8601String(),
                'narrator'     => $this->narrator->getComment('dungeon_failed'),
            ];
        }

        if ($isLastRoom) {
            // La zone ne se débloque que si les héros ont gagné le combat du boss
            $bossVictory = ($result['outcome'] ?? '') === 'victory';

            // Dungeon completed — apply completion bonus and set full cooldown
            $bonusGold = intdiv($dungeon->gold_gained * self::COMPLETION_GOLD_BONUS_PERCENT, 100);
            $dungeon->gold_gained = $dungeon->gold_gained + $bonusGold;

            // Credit gold to user
            $user->gold = $user->gold + $dungeon->gold_gained;
            $user->save();

            $cooldownHours     = $this->settings->get('DUNGEON_COOLDOWN_HOURS', 8);
            $dungeon->status       = 'completed';
            $dungeon->completed_at = now();
            $dungeon->available_at = now()->addHours($cooldownHours);
            $dungeon->save();

            // Marquer le boss de cette zone comme vaincu et débloquer la zone suivante
            $unlockedZoneName = null;
            if ($bossVictory) {
                $currentZone = $dungeon->zone;
                if ($currentZone) {
                    UserZoneProgress::updateOrCreate(
                        ['user_id' => $user->id, 'zone_id' => $currentZone->id],
                        ['boss_defeated' => true]
                    );

                    $nextZone = Zone::where('order_index', $currentZone->order_index + 1)->first();
                    if ($nextZone) {
                        UserZoneProgress::firstOrCreate(
                            ['user_id' => $user->id, 'zone_id' => $nextZone->id],
                            ['total_combats' => 0, 'total_victories' => 0, 'boss_defeated' => false]
                        );
                        $unlockedZoneName = $nextZone->name;
                    }
                }
            }

            return [
                'success'           => true,
                'room_result'       => $result,
                'dungeon_over'      => true,
                'outcome'           => $bossVictory ? 'completed' : 'boss_defeat',
                'gold_gained'       => $dungeon->gold_gained,
                'bonus_gold'        => $bonusGold,
                'loot_count'        => count($dungeon->loot_gained ?? []),
                'available_at'      => $dungeon->available_at->toIso8601String(),
                'unlocked_zone'     => $unlockedZoneName,
                'narrator'          => $this->narrator->getComment($bossVictory ? 'dungeon_completed' : 'dungeon_failed'),
            ];
        }

        // Advance to next room
        $dungeon->current_room = $dungeon->current_room + 1;
        $dungeon->save();

        $nextIndex   = $dungeon->current_room - 1;
        $nextRoomData = $rooms[$nextIndex] ?? null;

        return [
            'success'      => true,
            'room_result'  => $result,
            'dungeon_over' => false,
            'current_room' => $dungeon->current_room,
            'total_rooms'  => $dungeon->total_rooms,
            'next_room'    => $nextRoomData ? $this->buildRoomPreview($nextRoomData) : null,
            'gold_gained'  => $dungeon->gold_gained,
            'loot_count'   => count($dungeon->loot_gained ?? []),
        ];
    }

    /**
     * Abandon the dungeon — lose all progress, set 1-hour cooldown.
     */
    public function abandon(User $user, int $dungeonId): array
    {
        $dungeon = Dungeon::where('id', $dungeonId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$dungeon) {
            return ['success' => false, 'error' => 'Donjon introuvable ou déjà terminé.'];
        }

        $dungeon->status       = 'abandoned';
        $dungeon->completed_at = now();
        $dungeon->available_at = now()->addHour();
        $dungeon->save();

        return [
            'success'      => true,
            'message'      => 'Donjon abandonné. Vos héros rentrent la tête basse.',
            'available_at' => $dungeon->available_at->toIso8601String(),
            'narrator'     => $this->narrator->getComment('dungeon_abandoned'),
        ];
    }

    // -------------------------------------------------------------------------
    // Room generation
    // -------------------------------------------------------------------------

    /**
     * Generate an array of room objects for a dungeon in the given zone.
     */
    private function generateRooms(Zone $zone): array
    {
        $minRooms = $this->settings->get('DUNGEON_ROOMS_MIN', 5);
        $maxRooms = $this->settings->get('DUNGEON_ROOMS_MAX', 8);
        $total    = random_int($minRooms, $maxRooms);

        $rooms = [];

        for ($i = 1; $i <= $total; $i++) {
            if ($i === 1) {
                $type = 'combat';
                $difficulty = 'easy';
            } elseif ($i === $total) {
                $type = 'boss';
                $difficulty = 'boss';
            } else {
                $type = $this->rollRoomType();
                $difficulty = 'normal';
            }

            $rooms[] = $this->buildRoom($type, $difficulty, $zone, $i, $total);
        }

        return $rooms;
    }

    /**
     * Weighted random room type for middle rooms.
     */
    private function rollRoomType(): string
    {
        $total  = array_sum(self::ROOM_WEIGHTS);
        $roll   = random_int(1, $total);
        $cumulative = 0;

        foreach (self::ROOM_WEIGHTS as $type => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return $type;
            }
        }

        return 'combat';
    }

    /**
     * Build a single room data array.
     */
    private function buildRoom(string $type, string $difficulty, Zone $zone, int $roomNumber, int $total): array
    {
        // Pick a monster for combat/boss rooms
        $monsterId        = null;
        $monsterName      = null;
        $monsterLevel     = null;
        $monsterImagePath = null;
        if (in_array($type, ['combat', 'boss'])) {
            $monster          = Monster::where('zone_id', $zone->id)->where('is_active', true)->inRandomOrder()->first();
            $monsterId        = $monster?->id;
            $monsterName      = $monster?->name;
            $monsterLevel     = $monster?->level;
            $monsterImagePath = $monster?->image_path;
        }

        // Trap damage as percent of team HP — scales with zone level_max
        $trapDamagePercent = self::TRAP_DAMAGE_BASE_PERCENT + $zone->level_max;

        // Loot rarity hint for treasure rooms
        $lootRarity = match (true) {
            $roomNumber === $total  => 'rare',
            $roomNumber >= intdiv($total * 2, 3) => 'peu_commun',
            default                 => 'commun',
        };

        $descriptions = $this->roomDescriptions($type, $difficulty);

        return [
            'room_number'         => $roomNumber,
            'type'                => $type,
            'difficulty'          => $difficulty,
            'monster_id'          => $monsterId,
            'monster_name'        => $monsterName,
            'monster_level'       => $monsterLevel,
            'monster_image_path'  => $monsterImagePath,
            'loot_rarity'         => $lootRarity,
            'trap_damage_percent' => $trapDamagePercent,
            'is_completed'        => false,
            'description'         => $descriptions[array_rand($descriptions)],
            'result_summary'      => '',
        ];
    }

    /**
     * Humorous room descriptions by type/difficulty.
     */
    private function roomDescriptions(string $type, string $difficulty): array
    {
        return match ($type) {
            'combat' => $difficulty === 'easy'
                ? [
                    'Une salle d\'entrée. Un monstre peu impressionnant vous accueille.',
                    'Premier obstacle : une créature qui semble aussi surprise que vous.',
                    'L\'ennemi de service. Il a l\'air d\'attendre depuis un moment.',
                ]
                : [
                    'Une créature surgit des ombres. Elle a l\'air de mauvaise humeur.',
                    'Un monstre barre le passage. La négociation semble compromise.',
                    'Combat inévitable. Le Narrateur prend ses paris.',
                ],
            'boss'   => [
                'Le boss du donjon. Il est grand, il est méchant, et il vous méprise visiblement.',
                'Boss final. Tout ce chemin pour ça. Bonne chance. Vraiment.',
                'Le gardien ultime du donjon vous regarde avec dédain. Réciproque.',
            ],
            'treasure' => [
                'Un coffre brille dans l\'obscurité. Il n\'est probablement pas piégé.',
                'Des richesses abandonnées ! Ou un appât. Le Narrateur reste neutre.',
                'Trésor trouvé ! Votre équipe se jette dessus avec une dignité remarquable.',
            ],
            'trap' => [
                'Une salle anormalement silencieuse. Le Narrateur conseille la prudence.',
                'Le sol a l\'air bizarre. Vos héros l\'ont remarqué. Trop tard.',
                'Piège ! Votre équipe le voit. Votre équipe l\'active quand même.',
            ],
            'rest' => [
                'Une alcôve paisible. Un feu de camp improbable y brûle gaiement.',
                'Salle de repos ! Vos héros s\'y effondrent avec soulagement.',
                'Miracle : une salle sans danger. Profitez-en, ça ne dure pas.',
            ],
            default => ['Une salle mystérieuse. Le Narrateur hausse les épaules.'],
        };
    }

    // -------------------------------------------------------------------------
    // Room resolution
    // -------------------------------------------------------------------------

    /**
     * Dispatch room resolution by type.
     */
    private function resolveRoom(array $room, $heroes, Zone $zone, User $user, Dungeon $dungeon): array
    {
        return match ($room['type']) {
            'combat'   => $this->resolveCombatRoom($room, $heroes, $zone, $user, false),
            'boss'     => $this->resolveCombatRoom($room, $heroes, $zone, $user, true),
            'treasure' => $this->resolveTreasureRoom($room, $zone, $user),
            'trap'     => $this->resolveTrapRoom($room, $heroes, $zone),
            'rest'     => $this->resolveRestRoom($heroes),
            default    => ['summary' => 'Rien ne se passe. C\'est déjà ça.', 'gold' => 0, 'loot_items' => [], 'heroes_wiped' => false],
        };
    }

    /**
     * Resolve a combat or boss room using simplified power-ratio combat.
     *
     * Hero power vs monster power → win probability:
     *   winChance = heroPower × 100 / (heroPower + monsterPower)   [integer]
     * On win: grant XP + gold + maybe loot.
     * On loss: heroes take damage; if all reach 0 HP → wipe.
     */
    private function resolveCombatRoom(array $room, $heroes, Zone $zone, User $user, bool $isBoss): array
    {
        $bossHpMult = $this->settings->get('DUNGEON_BOSS_HP_MULT', 300);

        // Load monster
        $monster = $room['monster_id']
            ? Monster::find($room['monster_id'])
            : Monster::where('zone_id', $zone->id)->where('is_active', true)->inRandomOrder()->first();

        if (!$monster) {
            // No monster data — auto-win with minimal reward
            return [
                'summary'      => 'La menace a mystérieusement disparu. Le Narrateur note l\'anomalie.',
                'gold'         => random_int(1, 5),
                'loot_items'   => [],
                'heroes_wiped' => false,
                'combat_detail' => [],
            ];
        }

        // Hero team power (integer)
        $heroPower = 0;
        foreach ($heroes as $hero) {
            $heroPower += $hero->powerRating();
        }
        $heroPower = max(1, $heroPower);

        // Monster power — scale for boss
        $monsterHp  = $monster->base_hp;
        $monsterAtq = $monster->base_atq;
        $monsterDef = $monster->base_def;

        if ($isBoss) {
            $monsterHp  = intdiv($monsterHp  * $bossHpMult, 100);
            $monsterAtq = intdiv($monsterAtq * $bossHpMult, 100);
        }

        $monsterPower = max(1, intdiv(($monsterAtq + $monster->base_int) * $monsterHp * (100 + $monsterDef), 100));

        // Win probability (integer percent)
        $winChance = intdiv($heroPower * 100, $heroPower + $monsterPower);
        $winChance = max(5, min(95, $winChance)); // clamp 5–95

        $roll   = random_int(1, 100);
        $heroesWon = $roll <= $winChance;

        if ($heroesWon) {
            // Gold reward: random between monster gold_min and gold_max
            $gold = random_int($monster->gold_min, max($monster->gold_min, $monster->gold_max));
            if ($isBoss) {
                $gold = intdiv($gold * $bossHpMult, 100);
            }

            // XP (distributed across all active heroes — not critical path, just record)
            $xpBase     = $this->settings->get('XP_BASE_PER_KILL', 10);
            $xpPerKill  = $xpBase + $monster->level * $this->settings->get('XP_LEVEL_MULTIPLIER', 2);

            // Loot attempt
            $lootItems = [];
            $item = $this->loot->rollLoot($zone, $monster, $user);
            if ($item) {
                $lootItems[] = ['item_id' => $item->id, 'name' => $item->name, 'rarity' => $item->rarity];
            }

            $narratorKey = $isBoss ? 'dungeon_boss_defeated' : 'combat_win';
            $summary = $this->narrator->getComment($narratorKey, ['monster_name' => $monster->name]);

            return [
                'summary'       => $summary,
                'gold'          => $gold,
                'xp_per_hero'   => $xpPerKill,
                'loot_items'    => $lootItems,
                'heroes_wiped'  => false,
                'monster_name'       => $monster->name,
                'monster_image_path' => $monster->image_path,
                'win_chance'    => $winChance,
                'rolled'        => $roll,
                'outcome'       => 'victory',
            ];
        } else {
            // Heroes lose this room
            // Deal proportional damage to hero team (persist to DB)
            $damagePercent = intdiv(100 * $monsterPower, $heroPower + $monsterPower);
            $damagePercent = max(10, min(80, $damagePercent));

            $allDead = $this->applyDamageToHeroes($heroes, $damagePercent);

            $summary = $this->narrator->getComment('combat_defeat', ['monster_name' => $monster->name]);

            return [
                'summary'       => $summary,
                'gold'          => 0,
                'xp_per_hero'   => 0,
                'loot_items'    => [],
                'heroes_wiped'       => $allDead,
                'monster_name'       => $monster->name,
                'monster_image_path' => $monster->image_path,
                'win_chance'    => $winChance,
                'rolled'        => $roll,
                'outcome'       => 'defeat',
                'damage_percent' => $damagePercent,
            ];
        }
    }

    /**
     * Resolve a treasure room: grant 1–3 items of varying rarity.
     */
    private function resolveTreasureRoom(array $room, Zone $zone, User $user): array
    {
        $count     = random_int(1, 3);
        $lootItems = [];

        for ($i = 0; $i < $count; $i++) {
            $rarity = $room['loot_rarity'];
            // Chance to upgrade rarity for extra items
            if ($i > 0 && random_int(1, 100) <= 20) {
                $rarity = $this->loot->rollRarity();
            }

            $slot = $this->loot->rollSlot();
            $itemLevel = intdiv($zone->level_min + $zone->level_max, 2);

            $item = $this->loot->generateItemForCrafting($user, $rarity, $slot, $itemLevel);
            $lootItems[] = ['item_id' => $item->id, 'name' => $item->name, 'rarity' => $item->rarity];
        }

        $summary = $this->narrator->getComment('loot_found');

        return [
            'summary'      => $summary,
            'gold'         => 0,
            'loot_items'   => $lootItems,
            'heroes_wiped' => false,
            'items_found'  => count($lootItems),
        ];
    }

    /**
     * Resolve a trap room: deal trap_damage_percent% of total team HP, distributed evenly.
     */
    private function resolveTrapRoom(array $room, $heroes, Zone $zone): array
    {
        $damagePercent = $room['trap_damage_percent'] ?? self::TRAP_DAMAGE_BASE_PERCENT;

        $allDead = $this->applyDamageToHeroes($heroes, $damagePercent);

        $summary = $this->narrator->getComment('trap_triggered');

        return [
            'summary'        => $summary,
            'gold'           => 0,
            'loot_items'     => [],
            'heroes_wiped'   => $allDead,
            'damage_percent' => $damagePercent,
        ];
    }

    /**
     * Resolve a rest room: heal each hero by REST_HEAL_PERCENT% of their max_hp.
     */
    private function resolveRestRoom($heroes): array
    {
        foreach ($heroes as $hero) {
            $healAmount  = intdiv($hero->max_hp * self::REST_HEAL_PERCENT, 100);
            $newHp       = min($hero->max_hp, $hero->current_hp + $healAmount);
            $hero->current_hp = $newHp;
            $hero->save();
        }

        $summary = $this->narrator->getComment('rest_room');

        return [
            'summary'      => $summary,
            'gold'         => 0,
            'loot_items'   => [],
            'heroes_wiped' => false,
            'heal_percent' => self::REST_HEAL_PERCENT,
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Apply damagePercent% of each hero's max_hp as damage.
     * Returns true if all heroes are now at 0 HP (wipe).
     * All arithmetic is integer only.
     */
    private function applyDamageToHeroes($heroes, int $damagePercent): bool
    {
        foreach ($heroes as $hero) {
            $damage       = intdiv($hero->max_hp * $damagePercent, 100);
            $damage       = max(1, $damage);
            $newHp        = max(0, $hero->current_hp - $damage);
            $hero->current_hp = $newHp;
            $hero->save();
        }

        // Wipe if every hero is at 0 HP
        foreach ($heroes as $hero) {
            if ($hero->current_hp > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build a safe room preview (no internal state leaked).
     */
    private function buildRoomPreview(array $room): array
    {
        return [
            'room_number'         => $room['room_number'],
            'type'                => $room['type'],
            'difficulty'          => $room['difficulty'],
            'description'         => $room['description'],
            'is_completed'        => $room['is_completed'],
            'monster_name'        => $room['monster_name'] ?? null,
            'monster_level'       => $room['monster_level'] ?? null,
            'monster_image_path'  => $room['monster_image_path'] ?? null,
        ];
    }
}
