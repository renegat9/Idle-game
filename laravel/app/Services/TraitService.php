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
                $intBuff = $effectData['int_buff_percent'] ?? $this->settings->get('TRAIT_PHILOSOPHE_INT_BUFF', 15);
                // Le buff sera appliqué après le skip
                $combatState['pending_buffs'][] = [
                    'hero_id' => $hero->id,
                    'stat' => 'int',
                    'percent' => $intBuff,
                    'duration' => 1,
                ];
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
