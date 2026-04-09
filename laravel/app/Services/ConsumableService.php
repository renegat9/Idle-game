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
                    $amount = (int) ($hero->max_hp * (int) $consumable->effect_value / 100);
                    $healed = $this->healHero($hero, $amount);
                    $applied[] = ['hero' => $hero->name, 'healed' => $healed];
                }
                break;

            case 'xp_boost':
                foreach ($heroes as $hero) {
                    $hero->xp += (int) $consumable->effect_value;
                    // Vérifier montée de niveau
                    while ($hero->xp >= $hero->xp_to_next_level) {
                        $hero->xp -= $hero->xp_to_next_level;
                        $hero->level += 1;
                        $hero->xp_to_next_level = (int) ($hero->xp_to_next_level * 150 / 100);
                        $hero->talent_points += 1;
                    }
                    $hero->save();
                    $applied[] = ['hero' => $hero->name, 'xp_gained' => (int) $consumable->effect_value, 'new_level' => $hero->level];
                }
                break;

            case 'gold_boost':
                $amount = (int) $consumable->effect_value;
                $user->gold += $amount;
                $user->save();
                $applied[] = ['gold_gained' => $amount];
                break;

            case 'cure_debuff':
                foreach ($heroes as $hero) {
                    $removed = $hero->buffs()->where('duration_remaining', '>', 0)->delete();
                    $applied[] = ['hero' => $hero->name, 'debuffs_removed' => $removed];
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
