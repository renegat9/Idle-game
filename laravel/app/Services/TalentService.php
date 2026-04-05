<?php

namespace App\Services;

use App\Models\Hero;
use App\Models\HeroTalent;
use App\Models\Talent;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TalentService
{
    public function __construct(private readonly SettingsService $settings) {}

    // ─── Points ──────────────────────────────────────────────────────────────

    /**
     * Talent points earned = floor(level / 5), capped at MAX_TALENT_POINTS.
     */
    public function pointsForLevel(int $level): int
    {
        $maxPoints = $this->settings->get('MAX_TALENT_POINTS', 20);
        return min($maxPoints, intdiv($level, 5));
    }

    // ─── Tree ────────────────────────────────────────────────────────────────

    /**
     * Returns the full talent tree for the given hero, with unlock status.
     *
     * @return array{
     *   hero: array,
     *   points_available: int,
     *   reset_cost: int,
     *   branches: array{offensive: array, defensive: array, defaut: array}
     * }
     */
    public function getTree(Hero $hero): array
    {
        $classId        = $hero->class_id;
        $unlockedIds    = HeroTalent::where('hero_id', $hero->id)->pluck('talent_id')->all();

        $allTalents = Talent::where('class_id', $classId)
            ->orderBy('branch')
            ->orderBy('tier')
            ->orderBy('position')
            ->get();

        // Points spent per branch
        $branchPoints = ['offensive' => 0, 'defensive' => 0, 'defaut' => 0];
        foreach ($allTalents as $t) {
            if (in_array($t->id, $unlockedIds)) {
                $branchPoints[$t->branch] += $t->cost;
            }
        }

        $availablePoints = $hero->talent_points - array_sum($branchPoints);
        $resetCost       = $this->resetCost($hero);

        $branches = ['offensive' => [], 'defensive' => [], 'defaut' => []];
        foreach ($allTalents as $t) {
            $isUnlocked = in_array($t->id, $unlockedIds);
            $canUnlock  = !$isUnlocked
                && $availablePoints >= $t->cost
                && $branchPoints[$t->branch] >= $t->required_points_in_branch
                && ($t->prerequisite_talent_id === null || in_array($t->prerequisite_talent_id, $unlockedIds));

            $branches[$t->branch][] = [
                'id'                       => $t->id,
                'name'                     => $t->name,
                'description'              => $t->description,
                'branch'                   => $t->branch,
                'tier'                     => $t->tier,
                'position'                 => $t->position,
                'cost'                     => $t->cost,
                'required_points_in_branch' => $t->required_points_in_branch,
                'talent_type'              => $t->talent_type,
                'effect_data'              => $t->effect_data,
                'is_unlocked'              => $isUnlocked,
                'can_unlock'               => $canUnlock,
            ];
        }

        return [
            'hero'             => [
                'id'                  => $hero->id,
                'name'                => $hero->name,
                'level'               => $hero->level,
                'talent_points_total' => $hero->talent_points,
                'talent_reset_count'  => $hero->talent_reset_count,
            ],
            'points_available' => $availablePoints,
            'reset_cost'       => $resetCost,
            'branches'         => $branches,
        ];
    }

    // ─── Allocate ────────────────────────────────────────────────────────────

    /**
     * Allocate a talent point to a specific talent for the given hero.
     * Returns array with 'success', 'message', and updated 'points_available'.
     */
    public function allocate(Hero $hero, int $talentId): array
    {
        $talent = Talent::where('id', $talentId)
            ->where('class_id', $hero->class_id)
            ->first();

        if (!$talent) {
            return ['success' => false, 'message' => 'Talent introuvable pour cette classe.'];
        }

        $alreadyUnlocked = HeroTalent::where('hero_id', $hero->id)
            ->where('talent_id', $talentId)
            ->exists();

        if ($alreadyUnlocked) {
            return ['success' => false, 'message' => 'Ce talent est déjà débloqué.'];
        }

        // Count points spent per branch
        $spentInBranch = $this->spentInBranch($hero, $talent->branch);
        $totalSpent    = $this->totalSpent($hero);
        $available     = $hero->talent_points - $totalSpent;

        if ($available < $talent->cost) {
            return ['success' => false, 'message' => "Points insuffisants. Il vous faut {$talent->cost} point(s), vous en avez {$available}."];
        }

        if ($spentInBranch < $talent->required_points_in_branch) {
            return ['success' => false, 'message' => "Pas assez de points dans cette branche. Requis : {$talent->required_points_in_branch}, vous avez : {$spentInBranch}."];
        }

        if ($talent->prerequisite_talent_id !== null) {
            $prereqUnlocked = HeroTalent::where('hero_id', $hero->id)
                ->where('talent_id', $talent->prerequisite_talent_id)
                ->exists();
            if (!$prereqUnlocked) {
                return ['success' => false, 'message' => 'Le talent prérequis n\'est pas encore débloqué.'];
            }
        }

        HeroTalent::create([
            'hero_id'     => $hero->id,
            'talent_id'   => $talentId,
            'unlocked_at' => now(),
        ]);

        return [
            'success'          => true,
            'message'          => "Talent \"{$talent->name}\" débloqué ! Le héros semble légèrement moins incompétent.",
            'points_available' => $available - $talent->cost,
        ];
    }

    // ─── Reset ───────────────────────────────────────────────────────────────

    /**
     * Reset all talents for the given hero. Charges the reset cost in gold.
     * Returns array with 'success', 'message', 'gold_spent'.
     */
    public function reset(Hero $hero, User $user): array
    {
        $cost = $this->resetCost($hero);

        if ($user->gold < $cost) {
            return ['success' => false, 'message' => "Or insuffisant pour réinitialiser. Coût : {$cost} or, vous avez : {$user->gold} or."];
        }

        DB::transaction(function () use ($hero, $user, $cost) {
            HeroTalent::where('hero_id', $hero->id)->delete();
            $hero->increment('talent_reset_count');
            $user->decrement('gold', $cost);

            DB::table('economy_log')->insert([
                'user_id'          => $user->id,
                'transaction_type' => 'depense',
                'source'           => 'talent_reset',
                'amount'           => $cost,
                'balance_after'    => $user->gold - $cost,
                'description'      => "Réinitialisation des talents de {$hero->name}",
            ]);
        });

        return [
            'success'    => true,
            'message'    => "Talents réinitialisés pour {$hero->name}. Le Narrateur observe votre repentir avec satisfaction.",
            'gold_spent' => $cost,
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Total talent points spent by a hero (across all branches).
     */
    private function totalSpent(Hero $hero): int
    {
        return HeroTalent::where('hero_id', $hero->id)
            ->join('talents', 'talents.id', '=', 'hero_talents.talent_id')
            ->sum('talents.cost');
    }

    /**
     * Talent points spent in a specific branch.
     */
    private function spentInBranch(Hero $hero, string $branch): int
    {
        return HeroTalent::where('hero_id', $hero->id)
            ->join('talents', 'talents.id', '=', 'hero_talents.talent_id')
            ->where('talents.branch', $branch)
            ->sum('talents.cost');
    }

    /**
     * Cost to reset talents (escalates with reset count).
     * Formula: base × (150/100)^reset_count — integer only.
     */
    private function resetCost(Hero $hero): int
    {
        $base  = $this->settings->get('TALENT_RESET_BASE_COST', 200);
        $count = $hero->talent_reset_count;

        // Compute (150/100)^count using integer arithmetic
        $cost = $base;
        for ($i = 0; $i < $count; $i++) {
            $cost = intdiv($cost * 150, 100);
        }

        return max(1, $cost);
    }
}
