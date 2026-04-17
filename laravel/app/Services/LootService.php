<?php

namespace App\Services;

use App\Jobs\GenerateLootImage;
use App\Models\Item;
use App\Models\ItemTemplate;
use App\Models\Monster;
use App\Models\User;
use App\Models\Zone;

class LootService
{
    // Noms statiques humoristiques pour les rares (sans IA en Phase 1)
    private array $rareNameTemplates = [
        'arme' => [
            'L\'Épée qui Coupe (Parfois)',
            'Le Marteau du Destin Approximatif',
            'La Dague du Voleur Repenti',
            'Le Bâton du Mage Distrait',
            'L\'Arc de la Précision Relative',
        ],
        'armure' => [
            'La Cuirasse du Brave (Pas Très)',
            'La Robe de l\'Académie Douteuse',
            'Le Hauberk du Combattant Occasionnel',
            'L\'Armure Légère de la Discrétion',
        ],
        'casque' => [
            'Le Casque de la Pensée Profonde',
            'La Coiffe du Sage Amateur',
            'Le Heaume du Courage Relatif',
        ],
        'bottes' => [
            'Les Bottes de la Fuite Rapide',
            'Les Sandales de l\'Aventurier Survivant',
            'Les Bottines du Voleur Chanceux',
        ],
        'accessoire' => [
            'L\'Amulette du Chance-Peut-Être',
            'L\'Anneau du Pouvoir Modeste',
            'Le Talisman du Destin Flou',
        ],
        'truc_bizarre' => [
            'L\'Objet de Fonction Inconnue',
            'Le Truc Brillant Inexplicable',
            'La Chose du Donjon Profond',
        ],
    ];

    public function __construct(
        private readonly SettingsService $settings
    ) {}

    /**
     * Tente de générer un objet loot pour un monstre donné.
     * Retourne null si aucun objet ne drop.
     */
    public function rollLoot(Zone $zone, Monster $monster, User $user): ?Item
    {
        $dropChance = $this->settings->get('LOOT_DROP_CHANCE', 60);

        // Bonus de loot pour les élites/boss
        $bonus = $monster->loot_bonus;
        $effectiveChance = min(100, intdiv($dropChance * (100 + $bonus), 100));

        if (random_int(1, 100) > $effectiveChance) {
            return null;
        }

        $rarity = $this->rollRarity();
        $slot = $this->rollSlot();
        $itemLevel = $this->rollItemLevel($zone);

        return $this->generateItem($zone, $rarity, $slot, $itemLevel, $user);
    }

    /**
     * Roule la rareté selon les poids de game_settings.
     */
    public function rollRarity(): string
    {
        $weights = [
            'commun'      => $this->settings->get('LOOT_RARITY_COMMUN', 50),
            'peu_commun'  => $this->settings->get('LOOT_RARITY_PEU_COMMUN', 25),
            'rare'        => $this->settings->get('LOOT_RARITY_RARE', 14),
            'epique'      => $this->settings->get('LOOT_RARITY_EPIQUE', 7),
            'legendaire'  => $this->settings->get('LOOT_RARITY_LEGENDAIRE', 3),
            'wtf'         => $this->settings->get('LOOT_RARITY_WTF', 1),
        ];

        $total = array_sum($weights);
        $roll = random_int(1, $total);
        $cumulative = 0;

        foreach ($weights as $rarity => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return $rarity;
            }
        }

        return 'commun';
    }

    /**
     * Roule le slot avec poids.
     */
    public function rollSlot(): string
    {
        $weights = [
            'arme'        => 25,
            'armure'      => 20,
            'casque'      => 15,
            'bottes'      => 15,
            'accessoire'  => 20,
            'truc_bizarre' => 5,
        ];

        $total = array_sum($weights);
        $roll = random_int(1, $total);
        $cumulative = 0;

        foreach ($weights as $slot => $weight) {
            $cumulative += $weight;
            if ($roll <= $cumulative) {
                return $slot;
            }
        }

        return 'accessoire';
    }

    private function rollItemLevel(Zone $zone): int
    {
        $range = $this->settings->get('LOOT_LEVEL_RANGE', 2);
        $base = intdiv($zone->level_min + $zone->level_max, 2);
        $variation = random_int(-$range, $range);
        return max(1, $base + $variation);
    }

    private function generateItem(Zone $zone, string $rarity, string $slot, int $itemLevel, User $user): Item
    {
        $variance = $this->settings->get('LOOT_STAT_VARIANCE', 15);

        if (in_array($rarity, ['commun', 'peu_commun'])) {
            // Chercher un template
            $template = ItemTemplate::where('zone_id', $zone->id)
                ->where('rarity', $rarity)
                ->where('slot', $slot)
                ->inRandomOrder()
                ->first();

            if ($template) {
                return $this->generateFromTemplate($template, $itemLevel, $variance, $user);
            }
        }

        // Génération sans template (Rare+ ou template manquant)
        return $this->generateFallbackItem($zone, $rarity, $slot, $itemLevel, $variance, $user);
    }

    /**
     * Dispatch async image generation for Rare+ items.
     */
    private function dispatchImageJob(Item $item): void
    {
        $imageRarities = ['rare', 'epique', 'legendaire', 'wtf'];
        if (in_array($item->rarity, $imageRarities)) {
            GenerateLootImage::dispatch($item->id, $item->slot, $item->rarity);
        }
    }

    private function generateFromTemplate(ItemTemplate $template, int $itemLevel, int $variance, User $user): Item
    {
        $levelMult = $itemLevel;
        $sellPercent = $this->settings->get('LOOT_SELL_PERCENT', 30);

        $applyVariance = fn(int $base) => max(0, intdiv($base * $levelMult * random_int(100 - $variance, 100 + $variance), 100));

        $atq = $applyVariance($template->base_atq);
        $def = $applyVariance($template->base_def);
        $hp  = $applyVariance($template->base_hp);
        $vit = $applyVariance($template->base_vit);
        $cha = $applyVariance($template->base_cha);
        $int = $applyVariance($template->base_int);

        $totalStats = $atq + $def + $hp + $vit + $cha + $int;
        $sellValue = max(1, intdiv($totalStats * $sellPercent, 100) + $template->base_sell_value);

        $item = Item::create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'rarity' => $template->rarity,
            'slot' => $template->slot,
            'element' => $template->element,
            'item_level' => $itemLevel,
            'atq' => $atq, 'def' => $def, 'hp' => $hp,
            'vit' => $vit, 'cha' => $cha, 'int' => $int,
            'sell_value' => $sellValue,
            'is_ai_generated' => !empty($template->image_path) && !str_starts_with($template->image_path, 'images/placeholders/'),
            'image_url' => (!empty($template->image_path) && !str_starts_with($template->image_path, 'images/placeholders/'))
                ? $template->image_path
                : null,
        ]);

        $this->dispatchImageJob($item);

        return $item;
    }

    /**
     * Génère un objet loot pour une récompense de quête (sans zone ni monstre).
     * Le slot est tiré aléatoirement, le level basé sur le niveau moyen des héros actifs.
     */
    public function rollQuestLoot(User $user, string $rarity): Item
    {
        $slot      = $this->rollSlot();
        $heroLevel = (int) ($user->activeHeroes()->avg('level') ?? 1);
        $itemLevel = max(1, $heroLevel);

        return $this->generateItemForCrafting($user, $rarity, $slot, $itemLevel);
    }

    /**
     * Generate an item by rarity/slot without requiring a zone (used for crafting).
     */
    public function generateItemForCrafting(User $user, string $rarity, string $slot, int $itemLevel): Item
    {
        $variance = $this->settings->get('LOOT_STAT_VARIANCE', 15);

        // Try to find any template matching rarity/slot
        $template = ItemTemplate::where('rarity', $rarity)->where('slot', $slot)->inRandomOrder()->first();
        if ($template) {
            return $this->generateFromTemplate($template, $itemLevel, $variance, $user);
        }

        // Fallback without zone
        $rarityMultipliers = [
            'commun' => 1, 'peu_commun' => 2, 'rare' => 3,
            'epique' => 5, 'legendaire' => 8, 'wtf' => 10,
        ];
        $mult = $rarityMultipliers[$rarity] ?? 1;
        $baseStatValue = max(1, intdiv($itemLevel * $mult * random_int(100 - $variance, 100 + $variance), 100));

        $atq = $def = $hp = $vit = $cha = $int = 0;
        match ($slot) {
            'arme'        => $atq = $baseStatValue,
            'armure'      => $def = $baseStatValue,
            'casque'      => $def = intdiv($baseStatValue, 2),
            'bottes'      => $vit = $baseStatValue,
            'accessoire'  => $cha = $baseStatValue,
            default       => $hp  = $baseStatValue,
        };

        $names = $this->rareNameTemplates[$slot] ?? ['Objet Forgé'];
        $sellPercent = $this->settings->get('LOOT_SELL_PERCENT', 30);
        $totalStats  = $atq + $def + $hp + $vit + $cha + $int;
        $sellValue   = max(1, intdiv($totalStats * $sellPercent * $mult, 100));

        return Item::create([
            'user_id'        => $user->id,
            'name'           => $names[array_rand($names)] . ' (Forgé)',
            'description'    => 'Créé par fusion à la forge. Gérard est impressionné.',
            'rarity'         => $rarity,
            'slot'           => $slot,
            'element'        => 'physique',
            'item_level'     => $itemLevel,
            'atq' => $atq, 'def' => $def, 'hp' => $hp,
            'vit' => $vit, 'cha' => $cha, 'int' => $int,
            'sell_value'     => $sellValue,
            'is_ai_generated'=> false,
        ]);
    }

    private function generateFallbackItem(Zone $zone, string $rarity, string $slot, int $itemLevel, int $variance, User $user): Item
    {
        $rarityMultipliers = [
            'commun' => 1, 'peu_commun' => 2, 'rare' => 3,
            'epique' => 5, 'legendaire' => 8, 'wtf' => 10,
        ];
        $mult = $rarityMultipliers[$rarity] ?? 1;

        $baseStatValue = intdiv($itemLevel * $mult * random_int(100 - $variance, 100 + $variance), 100);
        $baseStatValue = max(1, $baseStatValue);

        // Stat principale selon le slot
        $atq = $def = $hp = $vit = $cha = $int = 0;
        match ($slot) {
            'arme'       => $atq = $baseStatValue,
            'armure'     => $def = $baseStatValue,
            'casque'     => $def = intdiv($baseStatValue, 2),
            'bottes'     => $vit = $baseStatValue,
            'accessoire' => $cha = $baseStatValue,
            'truc_bizarre' => $hp = $baseStatValue,
            default      => $hp = $baseStatValue,
        };

        $names = $this->rareNameTemplates[$slot] ?? ['Objet Mystérieux'];
        $name = $names[array_rand($names)];
        if ($rarity === 'epique') $name .= ' (Épique)';
        if ($rarity === 'legendaire') $name .= ' (LÉGENDAIRE)';
        if ($rarity === 'wtf') $name = '??? ' . $name . ' ???';

        $sellPercent = $this->settings->get('LOOT_SELL_PERCENT', 30);
        $totalStats = $atq + $def + $hp + $vit + $cha + $int;
        $sellValue = max(1, intdiv($totalStats * $sellPercent * $mult, 100));

        $item = Item::create([
            'user_id' => $user->id,
            'name' => $name,
            'description' => 'Trouvé dans ' . $zone->name . '. Gérard en serait jaloux.',
            'rarity' => $rarity,
            'slot' => $slot,
            'element' => $zone->dominant_element,
            'item_level' => $itemLevel,
            'atq' => $atq, 'def' => $def, 'hp' => $hp,
            'vit' => $vit, 'cha' => $cha, 'int' => $int,
            'sell_value' => $sellValue,
            'is_ai_generated' => false,
        ]);

        $this->dispatchImageJob($item);

        return $item;
    }

    public function calculateSellValue(Item $item): int
    {
        return $item->sell_value;
    }

    /**
     * Tente de dropper un matériau de forge parmi les matériaux disponibles pour cette zone.
     * Retourne [slug, name, qty] ou null.
     *
     * @param \Illuminate\Support\Collection $availableMaterials  (slug, name, drop_chance)
     * @param int $eliteLootMult  multiplicateur élite (100 = normal)
     */
    public function rollMaterialDrop(\Illuminate\Support\Collection $availableMaterials, int $eliteLootMult = 100): ?array
    {
        $baseChance = $this->settings->get('EXPLORATION_MATERIAL_CHANCE', 50);
        $effectiveChance = min(100, intdiv($baseChance * $eliteLootMult, 100));

        if (random_int(1, 100) > $effectiveChance) {
            return null;
        }

        $droppable = $availableMaterials->where('drop_chance', '>', 0);
        if ($droppable->isEmpty()) {
            return null;
        }

        // Sélection pondérée par drop_chance
        $totalWeight = (int) $droppable->sum('drop_chance');
        $roll = random_int(1, max(1, $totalWeight));
        $cumulative = 0;
        foreach ($droppable as $mat) {
            $cumulative += $mat->drop_chance;
            if ($roll <= $cumulative) {
                // Matériaux communs (drop_chance élevé) → plus grande quantité
                $maxQty = $mat->drop_chance >= 20 ? 3 : ($mat->drop_chance >= 5 ? 2 : 1);
                return [
                    'slug' => $mat->slug,
                    'name' => $mat->name,
                    'qty'  => random_int(1, $maxQty),
                ];
            }
        }

        return null;
    }
}
