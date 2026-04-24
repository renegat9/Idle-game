<?php

namespace App\Services;

use App\Models\Hero;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConsumableService
{
    /**
     * Retourne l'inventaire consommables du joueur (avec les données de référence).
     */
    public function inventory(User $user): array
    {
        return DB::table('user_consumables as uc')
            ->join('consumables as c', 'c.slug', '=', 'uc.consumable_slug')
            ->where('uc.user_id', $user->id)
            ->where('uc.quantity', '>', 0)
            ->select('c.*', 'uc.quantity', 'uc.id as user_consumable_id')
            ->get()
            ->map(fn($row) => (array) $row)
            ->values()
            ->toArray();
    }

    /**
     * Ajoute `qty` exemplaires d'un consommable au joueur (ou crée l'entrée).
     */
    public function addToInventory(User $user, string $slug, int $qty = 1): void
    {
        $consumable = DB::table('consumables')->where('slug', $slug)->first();
        if (!$consumable) {
            return;
        }

        $existing = DB::table('user_consumables')
            ->where('user_id', $user->id)
            ->where('consumable_slug', $slug)
            ->first();

        if ($existing) {
            $newQty = min((int) $existing->quantity + $qty, (int) $consumable->stack_max);
            DB::table('user_consumables')
                ->where('id', $existing->id)
                ->update(['quantity' => $newQty]);
        } else {
            DB::table('user_consumables')->insert([
                'user_id'          => $user->id,
                'consumable_slug'  => $slug,
                'quantity'         => min($qty, (int) $consumable->stack_max),
                'obtained_at'      => now(),
            ]);
        }
    }

    /**
     * Utilise un consommable (slug) sur l'équipe du joueur.
     * Retourne le résultat appliqué ou lance une exception métier.
     */
    public function use(User $user, string $slug): array
    {
        return DB::transaction(function () use ($user, $slug) {
            $row = DB::table('user_consumables')
                ->where('user_id', $user->id)
                ->where('consumable_slug', $slug)
                ->lockForUpdate()
                ->first();

            if (!$row || (int) $row->quantity <= 0) {
                throw new \RuntimeException('Vous n\'avez pas ce consommable.');
            }

            $consumable = DB::table('consumables')->where('slug', $slug)->first();
            if (!$consumable) {
                throw new \RuntimeException('Consommable inconnu.');
            }

            $heroes = $user->heroes()->where('is_active', true)->get();
            if ($heroes->isEmpty()) {
                throw new \RuntimeException('Aucun héros actif pour utiliser ce consommable.');
            }

            if (in_array($consumable->effect_type, ['heal_hp', 'restore_hp_pct'])) {
                $allFull = $heroes->every(fn($h) => $h->current_hp >= $h->max_hp);
                if ($allFull) {
                    throw new \RuntimeException('Tous vos héros sont déjà à pleine santé !');
                }
            }

            $result = $this->applyEffect($user, $heroes, $consumable);

            // Décrémenter la quantité
            DB::table('user_consumables')
                ->where('id', $row->id)
                ->decrement('quantity');

            return [
                'consumable_name' => $consumable->name,
                'effect_type'     => $consumable->effect_type,
                'effect_value'    => (int) $consumable->effect_value,
                'remaining'       => (int) $row->quantity - 1,
                'applied_to'      => $result,
            ];
        });
    }

    private function applyEffect(User $user, \Illuminate\Support\Collection $heroes, object $consumable): array
    {
        $applied = [];

        switch ($consumable->effect_type) {
            case 'heal_hp':
                foreach ($heroes as $hero) {
                    $healed = $this->healHero($hero, (int) $consumable->effect_value);
                    $applied[] = ['hero' => $hero->name, 'healed' => $healed];
                }
                break;

            case 'restore_hp_pct':
                foreach ($heroes as $hero) {
                    $amount = intdiv($hero->max_hp * (int) $consumable->effect_value, 100);
                    $healed = $this->healHero($hero, $amount);
                    $applied[] = ['hero' => $hero->name, 'healed' => $healed];
                }
                break;

            case 'xp_boost':
                foreach ($heroes as $hero) {
                    $hero->xp += (int) $consumable->effect_value;
                    while ($hero->xp >= $hero->xp_to_next_level) {
                        $hero->xp -= $hero->xp_to_next_level;
                        $hero->level += 1;
                        $hero->xp_to_next_level = intdiv($hero->xp_to_next_level * 150, 100);
                        $hero->talent_points += 1;
                    }
                    $hero->save();
                    $applied[] = ['hero' => $hero->name, 'xp_gained' => (int) $consumable->effect_value, 'new_level' => $hero->level];
                }
                $user->recalculateLevel();
                break;

            case 'gold_boost':
                $amount = (int) $consumable->effect_value;
                $user->gold += $amount;
                $user->save();
                $applied[] = ['gold_gained' => $amount];
                break;

            case 'cure_debuff':
                foreach ($heroes as $hero) {
                    $removed = $hero->buffs()->where('is_debuff', true)->where('remaining_combats', '>', 0)->delete();
                    DB::table('hero_combat_status_effects')
                        ->where('hero_id', $hero->id)
                        ->delete();
                    $applied[] = ['hero' => $hero->name, 'debuffs_removed' => $removed];
                }
                break;

            case 'cure_poison':
                foreach ($heroes as $hero) {
                    $removed = DB::table('hero_combat_status_effects')
                        ->where('hero_id', $hero->id)
                        ->where('effect_slug', 'empoisonne')
                        ->delete();
                    $applied[] = ['hero' => $hero->name, 'poison_removed' => $removed > 0];
                }
                break;

            case 'buff_atq_pct':
            case 'buff_def_pct':
            case 'buff_vit_pct':
                $statMap = ['buff_atq_pct' => 'atq', 'buff_def_pct' => 'def', 'buff_vit_pct' => 'vit'];
                $stat    = $statMap[$consumable->effect_type];
                $pct     = (int) $consumable->effect_value;
                $turns   = (int) $consumable->duration_turns;
                foreach ($heroes as $hero) {
                    $hero->buffs()->create([
                        'buff_key'          => $consumable->effect_type,
                        'name'              => $consumable->name,
                        'is_buff'           => true,
                        'is_debuff'         => false,
                        'value'             => 0,
                        'modifier_percent'  => $pct,
                        'stat_affected'     => $stat,
                        'remaining_combats' => max(1, $turns),
                        'source'            => 'consumable',
                    ]);
                    $applied[] = ['hero' => $hero->name, 'stat' => $stat, 'bonus_pct' => $pct, 'combats' => max(1, $turns)];
                }
                break;

            case 'guaranteed_flee':
                foreach ($heroes as $hero) {
                    $hero->buffs()->create([
                        'buff_key'          => 'guaranteed_flee',
                        'name'              => $consumable->name,
                        'is_buff'           => true,
                        'is_debuff'         => false,
                        'value'             => 1,
                        'modifier_percent'  => 0,
                        'stat_affected'     => 'none',
                        'remaining_combats' => 1,
                        'source'            => 'consumable',
                    ]);
                    $applied[] = ['hero' => $hero->name, 'flee_guaranteed' => true];
                }
                break;

            case 'repair_durability':
                $repairAmount = (int) $consumable->effect_value;
                $item = DB::table('items')
                    ->whereNotNull('equipped_by_hero_id')
                    ->whereIn('equipped_by_hero_id', $heroes->pluck('id'))
                    ->where('durability_current', '<', DB::raw('durability_max'))
                    ->orderByRaw('durability_max - durability_current DESC')
                    ->first();
                if ($item) {
                    $newDurability = min((int) $item->durability_max, (int) $item->durability_current + $repairAmount);
                    DB::table('items')->where('id', $item->id)->update(['durability_current' => $newDurability]);
                    $applied[] = ['item' => $item->name, 'repaired' => $newDurability - (int) $item->durability_current];
                } else {
                    $applied[] = ['info' => 'Tous les objets sont déjà en parfait état.'];
                }
                break;

            case 'dungeon_torch':
                foreach ($heroes as $hero) {
                    $existing = $hero->buffs()->where('buff_key', 'dungeon_torch')->where('remaining_combats', '>', 0)->first();
                    if ($existing) {
                        $existing->increment('remaining_combats', 10);
                    } else {
                        $hero->buffs()->create([
                            'buff_key'          => 'dungeon_torch',
                            'name'              => $consumable->name,
                            'is_buff'           => true,
                            'is_debuff'         => false,
                            'value'             => 1,
                            'modifier_percent'  => 0,
                            'stat_affected'     => 'none',
                            'remaining_combats' => 10,
                            'source'            => 'consumable',
                        ]);
                    }
                    $applied[] = ['hero' => $hero->name, 'torch_active' => true];
                }
                break;

            default:
                $applied[] = ['info' => 'Effet inconnu, mais ça a l\'air d\'avoir marché.'];
        }

        return $applied;
    }

    private function healHero(Hero $hero, int $amount): int
    {
        $before = $hero->current_hp;
        $hero->current_hp = min($hero->current_hp + $amount, $hero->max_hp);
        $hero->save();
        return $hero->current_hp - $before;
    }
}
