<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReputationService
{
    public function __construct(
        private readonly SettingsService $settings
    ) {}

    /**
     * Retourne la réputation du joueur pour toutes les zones (ou une zone spécifique).
     */
    public function getReputation(int $userId, ?int $zoneId = null): array
    {
        $query = DB::table('zone_reputation')
            ->join('zones', 'zones.id', '=', 'zone_reputation.zone_id')
            ->where('zone_reputation.user_id', $userId)
            ->select(
                'zone_reputation.zone_id',
                'zones.name as zone_name',
                'zones.slug as zone_slug',
                'zone_reputation.reputation'
            );

        if ($zoneId !== null) {
            $query->where('zone_reputation.zone_id', $zoneId);
        }

        return $query->get()->map(fn ($row) => [
            'zone_id'    => $row->zone_id,
            'zone_name'  => $row->zone_name,
            'zone_slug'  => $row->zone_slug,
            'reputation' => $row->reputation,
            'tier'       => $this->getReputationTier($row->reputation),
        ])->toArray();
    }

    /**
     * Ajoute des points de réputation pour une zone.
     * Retourne la nouvelle valeur et si un seuil a été franchi.
     */
    public function addReputation(int $userId, int $zoneId, int $points): array
    {
        $max = $this->settings->get('REPUTATION_MAX', 200);

        $existing = DB::table('zone_reputation')
            ->where('user_id', $userId)
            ->where('zone_id', $zoneId)
            ->value('reputation') ?? 0;

        $oldTier = $this->getReputationTier($existing);

        $newValue = min($existing + $points, $max);

        DB::table('zone_reputation')->updateOrInsert(
            ['user_id' => $userId, 'zone_id' => $zoneId],
            ['reputation' => $newValue, 'updated_at' => now(), 'created_at' => now()]
        );

        $newTier = $this->getReputationTier($newValue);
        $tierUp  = $newTier !== $oldTier;

        return [
            'zone_id'    => $zoneId,
            'reputation' => $newValue,
            'tier'       => $newTier,
            'tier_up'    => $tierUp,
            'old_tier'   => $oldTier,
            'capped'     => $newValue >= $max,
        ];
    }

    /**
     * Retire des points de réputation (min 0).
     */
    public function removeReputation(int $userId, int $zoneId, int $points): int
    {
        $existing = DB::table('zone_reputation')
            ->where('user_id', $userId)
            ->where('zone_id', $zoneId)
            ->value('reputation') ?? 0;

        $newValue = max(0, $existing - $points);

        DB::table('zone_reputation')->updateOrInsert(
            ['user_id' => $userId, 'zone_id' => $zoneId],
            ['reputation' => $newValue, 'updated_at' => now(), 'created_at' => now()]
        );

        return $newValue;
    }

    /**
     * Palier de réputation (utilisé pour les bonus de boutique/quêtes).
     * 0-24   → étranger
     * 25-49  → neutre
     * 50-99  → ami
     * 100-149 → honoré
     * 150-199 → révéré
     * 200    → exalté
     */
    public function getReputationTier(int $reputation): string
    {
        if ($reputation >= 200) return 'exalte';
        if ($reputation >= 150) return 'revere';
        if ($reputation >= 100) return 'honore';
        if ($reputation >= 50)  return 'ami';
        if ($reputation >= 25)  return 'neutre';
        return 'etranger';
    }

    /**
     * Bonus de loot selon réputation (en centièmes).
     * Exalté = +20%, Révéré = +15%, Honoré = +10%, Ami = +5%, sinon 0.
     */
    public function getLootBonus(int $userId, int $zoneId): int
    {
        $rep = DB::table('zone_reputation')
            ->where('user_id', $userId)
            ->where('zone_id', $zoneId)
            ->value('reputation') ?? 0;

        $tier = $this->getReputationTier($rep);

        return match ($tier) {
            'exalte'  => 20,
            'revere'  => 15,
            'honore'  => 10,
            'ami'     => 5,
            default   => 0,
        };
    }
}
