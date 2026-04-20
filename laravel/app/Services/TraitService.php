<?php

namespace App\Services;

use App\Models\Hero;

class TraitService
{
    public function __construct(
        private readonly SettingsService $settings
    ) {}

    /**
     * Détermine si le trait d'un héros se déclenche ce tour.
     */
    public function shouldTrigger(Hero $hero): bool
    {
        $trait = $hero->trait_;
        if (!$trait) {
            return false;
        }

        $chance = $this->getCurrentChance($hero);

        return random_int(1, 100) <= $chance;
    }

    /**
     * Retourne la chance effective selon le niveau du héros.
     */
    public function getCurrentChance(Hero $hero): int
    {
        $trait = $hero->trait_;
        if (!$trait) {
            return 0;
        }

        $level = $hero->level;

        if ($level >= 76) {
            return (int) $trait->chance_level_76;
        }
        if ($level >= 51) {
            return (int) $trait->chance_level_51;
        }
        if ($level >= 26) {
            return (int) $trait->chance_level_26;
        }

        return (int) $trait->base_chance;
    }

    /**
     * Applique l'effet du trait dans le contexte de combat.
     * Retourne un tableau décrivant ce qui s'est passé.
     */
    public function applyTraitEffect(Hero $hero, array &$combatState, string $context = 'combat'): array
    {
        $trait = $hero->trait_;
        if (!$trait) {
            return [];
        }

        $effectData = $trait->effect_data ?? [];
        $action = $effectData['action'] ?? 'none';
        $event = [
            'trait_slug' => $trait->slug,
            'trait_name' => $trait->name,
            'hero_name' => $hero->name,
            'action' => $action,
            'skip_turn' => $effectData['skip_turn'] ?? false,
            'narrator_key' => 'trait_triggered_' . $trait->slug,
        ];

        switch ($action) {
            case 'flee':
                $combatState['fled_heroes'][] = $hero->id;
                $event['message'] = $hero->name . ' prend ses jambes à son cou. Le Narrateur approuve.';
                break;

            case 'sleep':
                $duration = $effectData['duration'] ?? 2;
                $combatState['sleeping'][$hero->id] = $duration;
                $event['message'] = $hero->name . ' s\'endort. Zzz.';
                break;

            case 'steal_loot':
                // Marqué pour traitement post-combat
                $combatState['kleptomane_hero_id'] = $hero->id;
                $event['message'] = $hero->name . ' glisse discrètement un objet dans sa poche.';
                break;

            case 'aoe_fire':
                $stats = $hero->computedStats();
                $damagePercent = $effectData['damage_percent'] ?? $this->settings->get('TRAIT_PYROMANE_DAMAGE_PERCENT', 8);
                $fireDamage = max(1, intdiv($stats['atq'] * $damagePercent, 100));
                $friendlyFire = (bool) ($effectData['friendly_fire'] ?? $this->settings->get('TRAIT_PYROMANE_FRIENDLY_FIRE', 1));

                // Appliquer aux ennemis
                foreach ($combatState['enemies'] as &$enemy) {
                    if ($enemy['current_hp'] > 0) {
                        $enemy['current_hp'] = max(0, $enemy['current_hp'] - $fireDamage);
                    }
                }
                unset($enemy);

                // Friendly fire (alliés inclus sauf le pyromane lui-même)
                if ($friendlyFire) {
                    foreach ($combatState['heroes'] as &$ally) {
                        if (($ally['hero_id'] ?? null) !== $hero->id && $ally['current_hp'] > 0) {
                            $ally['current_hp'] = max(0, $ally['current_hp'] - intdiv($fireDamage, 2));
                        }
                    }
                    unset($ally);
                }

                $event['fire_damage'] = $fireDamage;
                $event['message'] = $hero->name . ' met le feu à TOUT. ' . $fireDamage . ' dégâts de feu. Partout.';
                break;

            case 'sneeze':
                $combatState['revealed'] = true;
                $event['message'] = 'ATCHOUM ! ' . $hero->name . ' vient de révéler la position de toute l\'équipe.';
                break;

            case 'ponder':
                // INT buff accumulé dans pending_buffs[hero_id] (entier %)
                // CombatService::consumePendingIntBuffs() l'applique au tour suivant
                $intBuff = $effectData['int_buff_percent'] ?? $this->settings->get('TRAIT_PHILOSOPHE_INT_BUFF', 5);
                $combatState['pending_buffs'][$hero->id] = ($combatState['pending_buffs'][$hero->id] ?? 0) + $intBuff;
                $event['message'] = $hero->name . ' réfléchit au sens du combat. Il ne participera pas à ce tour.';
                break;

            case 'consume_potion':
                $combatState['consumed_potion_hero_id'] = $hero->id;
                $event['message'] = $hero->name . ' vient de boire la potion de soin. Il n\'en avait pas besoin.';
                break;

            case 'refuse_dungeon':
                $combatState['blocked_heroes'][] = $hero->id;
                $event['message'] = $hero->name . ' refuse catégoriquement d\'entrer. Pas un mardi !';
                break;

            case 'refuse_attack':
                $event['message'] = $hero->name . ' ne peut pas attaquer. Regardez sa petite tête !';
                break;

            default:
                $event['message'] = $hero->name . ' fait une chose bizarre liée à son trait.';
                break;
        }

        return $event;
    }

    // ─── Synergies cachées ───────────────────────────────────────────────────

    /**
     * Registre des synergies classe + trait.
     * Clé : "class_slug|trait_slug"
     * Integer-only : tous les bonus en pourcentages entiers.
     */
    private const SYNERGY_REGISTRY = [
        // Voleur + Kleptomane → vole encore plus de loot
        'voleur|kleptomane' => [
            'slug'        => 'voleur_kleptomane',
            'name'        => 'Doigts de Fée (Usure Criminelle)',
            'description' => 'Le Voleur Kleptomane ne résiste pas. Butin +50%, mais il "emprunte" aussi l\'équipement de ses alliés.',
            'loot_bonus_pct'  => 50,
            'atq_bonus_pct'   => 0,
            'def_penalty_pct' => 5, // il "emprunte" aussi les affaires des alliés
        ],
        // Barbare + Pyromane → dégâts de zone massifs
        'barbare|pyromane' => [
            'slug'        => 'barbare_pyromane',
            'name'        => 'Rage Incendiaire',
            'description' => 'Le Barbare Pyromane met littéralement tout en feu. ATQ +30%, mais 10% de chances de brûler un allié.',
            'loot_bonus_pct'   => 0,
            'atq_bonus_pct'    => 30,
            'self_damage_pct'  => 10,
        ],
        // Barde + Narcoleptique → berceuse surpuissante
        'barde|narcoleptique' => [
            'slug'        => 'barde_narcoleptique',
            'name'        => 'Berceuse Mortelle',
            'description' => 'Le Barde Narcoleptique endort TOUT le monde. Ennemis −40% VIT, alliés aussi −20% VIT. Magnifique.',
            'loot_bonus_pct'       => 0,
            'atq_bonus_pct'        => 0,
            'enemy_vit_debuff_pct' => 40,
            'ally_vit_debuff_pct'  => 20,
        ],
        // Prêtre + Couard → fuite sanctifiée (survivabilité extrême)
        'pretre|couard' => [
            'slug'        => 'pretre_couard',
            'name'        => 'Retraite Bénie',
            'description' => 'Le Prêtre Couard bénit chaque fuite. DEF +25%, et les soins critiques arrivent toujours en fuyant.',
            'loot_bonus_pct'  => 0,
            'atq_bonus_pct'   => 0,
            'def_bonus_pct'   => 25,
        ],
        // Mage + Philosophe → contemplation infinie (INT × 2)
        'mage|philosophe' => [
            'slug'        => 'mage_philosophe',
            'name'        => 'Paralysie Philosophique',
            'description' => 'Le Mage Philosophe refuse d\'agir sans comprendre le sens profond de la magie. INT +40%, mais VIT −30%.',
            'loot_bonus_pct'  => 0,
            'atq_bonus_pct'   => 0,
            'int_bonus_pct'   => 40,
            'vit_penalty_pct' => 30,
        ],
        // Nécromancien + Pacifiste → armée morte très polie
        'necromancien|pacifiste' => [
            'slug'        => 'necromancien_pacifiste',
            'name'        => 'Morts-Vivants Diplomates',
            'description' => 'Le Nécromancien Pacifiste essaie de négocier avec ses propres squelettes. Loot +20%, dégâts −15%.',
            'loot_bonus_pct'  => 20,
            'atq_penalty_pct' => 15,
        ],
        // Ranger + Mythomane → tirs "légendaires" jamais vus
        'ranger|mythomane' => [
            'slug'        => 'ranger_mythomane',
            'name'        => 'L\'Exploit Imaginaire',
            'description' => 'Le Ranger Mythomane raconte ses exploits si souvent qu\'il finit par y croire. ATQ +20%, 15% d\'esquive.',
            'loot_bonus_pct'  => 0,
            'atq_bonus_pct'   => 20,
            'dodge_bonus_pct' => 15,
        ],
    ];

    /**
     * Retourne la synérgie d'un héros s'il en a une, null sinon.
     */
    public function checkSynergy(Hero $hero): ?array
    {
        if (!$hero->gameClass || !$hero->trait_) {
            return null;
        }

        $key = $hero->gameClass->slug . '|' . $hero->trait_->slug;

        return self::SYNERGY_REGISTRY[$key] ?? null;
    }

    /**
     * Agrège les bonus de synérgies de toute l'équipe.
     * Retourne un tableau de modificateurs additifs (integer-only).
     */
    public function getTeamSynergyModifiers(\Illuminate\Support\Collection $heroes): array
    {
        $modifiers = [
            'loot_bonus_pct'       => 0,
            'atq_bonus_pct'        => 0,
            'def_bonus_pct'        => 0,
            'def_penalty_pct'      => 0,
            'atq_penalty_pct'      => 0,
            'int_bonus_pct'        => 0,
            'vit_penalty_pct'      => 0,
            'self_damage_pct'      => 0,
            'enemy_vit_debuff_pct' => 0,
            'ally_vit_debuff_pct'  => 0,
            'dodge_bonus_pct'      => 0,
            'active_synergies'     => [],
        ];

        foreach ($heroes as $hero) {
            $synergy = $this->checkSynergy($hero);
            if (!$synergy) {
                continue;
            }

            $modifiers['active_synergies'][] = [
                'hero_name'   => $hero->name,
                'slug'        => $synergy['slug'],
                'name'        => $synergy['name'],
                'description' => $synergy['description'],
            ];

            foreach (['loot_bonus_pct', 'atq_bonus_pct', 'def_bonus_pct', 'def_penalty_pct', 'atq_penalty_pct',
                      'int_bonus_pct', 'vit_penalty_pct', 'self_damage_pct',
                      'enemy_vit_debuff_pct', 'ally_vit_debuff_pct', 'dodge_bonus_pct'] as $key) {
                $modifiers[$key] += (int) ($synergy[$key] ?? 0);
            }
        }

        return $modifiers;
    }

    /**
     * Retourne le total de points investis dans la branche du défaut du héros.
     * Utilisé pour activer les synergies Branche du Défaut × trait.
     */
    public function getDefautBranchPoints(Hero $hero): int
    {
        if (!$hero->relationLoaded('talents')) {
            $hero->load('talents');
        }
        return $hero->talents->where('branch', 'defaut')->sum('cost');
    }

    /**
     * Calcule l'impact d'un trait sur la puissance offline (simplification).
     */
    public function getOfflinePowerMultiplier(Hero $hero): int
    {
        $trait = $hero->trait_;
        if (!$trait) {
            return 100;
        }

        // Multiplicateurs de puissance offline (%) par trait slug
        $multipliers = [
            'couard'       => 85,
            'narcoleptique' => 80,
            'kleptomane'   => 100,
            'pyromane'     => 110, // Bonus offensif
            'allergique'   => 95,
            'philosophe'   => 95,
            'gourmand'     => 100,
            'superstitieux' => 100,
            'mythomane'    => 100,
            'pacifiste'    => 92,
        ];

        return $multipliers[$trait->slug] ?? 100;
    }
}
