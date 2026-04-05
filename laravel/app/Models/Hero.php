<?php

namespace App\Models;

use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hero extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'race_id', 'class_id', 'trait_id', 'name',
        'level', 'xp', 'xp_to_next_level', 'current_hp', 'max_hp',
        'talent_points', 'slot_index', 'is_active', 'deaths',
    ];

    protected $casts = [
        'level' => 'integer',
        'xp' => 'integer',
        'xp_to_next_level' => 'integer',
        'current_hp' => 'integer',
        'max_hp' => 'integer',
        'talent_points' => 'integer',
        'slot_index' => 'integer',
        'is_active' => 'boolean',
        'deaths' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function race()
    {
        return $this->belongsTo(Race::class);
    }

    public function gameClass()
    {
        return $this->belongsTo(GameClass::class, 'class_id');
    }

    public function trait_()
    {
        return $this->belongsTo(Trait_::class, 'trait_id');
    }

    public function heroTalents()
    {
        return $this->hasMany(HeroTalent::class);
    }

    public function buffs()
    {
        return $this->hasMany(HeroBuff::class);
    }

    public function equippedItems()
    {
        return $this->hasMany(Item::class, 'equipped_by_hero_id');
    }

    /**
     * Calcule les stats finales du héros.
     * stat = base_race + mod_classe + scaling_niveau + bonus_équipement
     * Tout en entiers, aucun float.
     */
    public function computedStats(): array
    {
        $settings = app(SettingsService::class);
        $race = $this->race;
        $class = $this->gameClass;

        $primaryStats = $class->primary_stats ?? [];
        $level = $this->level;
        $scalingPrimary = $settings->get('LEVEL_SCALING_FACTOR', 3);
        $scalingSecondary = $settings->get('LEVEL_SCALING_SECONDARY', 1);

        $stats = [
            'hp'  => $race->base_hp  + $class->mod_hp,
            'atq' => $race->base_atq + $class->mod_atq,
            'def' => $race->base_def + $class->mod_def,
            'vit' => $race->base_vit + $class->mod_vit,
            'cha' => $race->base_cha + $class->mod_cha,
            'int' => $race->base_int + $class->mod_int,
        ];

        // Scaling par niveau (entier)
        foreach ($stats as $stat => $base) {
            $scaling = in_array($stat, $primaryStats) ? $scalingPrimary : $scalingSecondary;
            $stats[$stat] = $base + ($level - 1) * $scaling;
        }

        // Bonus d'équipement
        $equippedItems = $this->relationLoaded('equippedItems')
            ? $this->equippedItems
            : $this->equippedItems()->get();

        foreach ($equippedItems as $item) {
            $stats['hp']  += $item->hp;
            $stats['atq'] += $item->atq;
            $stats['def'] += $item->def;
            $stats['vit'] += $item->vit;
            $stats['cha'] += $item->cha;
            $stats['int'] += $item->int;
        }

        // Garantir valeurs minimales positives (entiers)
        foreach ($stats as $stat => $value) {
            $stats[$stat] = max(1, (int) $value);
        }

        // HP max = stat HP calculée, HP actuel séparé
        $stats['max_hp'] = $stats['hp'];
        $stats['current_hp'] = $this->current_hp;

        return $stats;
    }

    /**
     * Calcule la puissance brute pour le calcul offline.
     * Power = (ATQ + INT) × HP × (100 + DEF) / 100
     * Résultat entier.
     */
    public function powerRating(): int
    {
        $stats = $this->computedStats();
        return intdiv(
            ($stats['atq'] + $stats['int']) * $stats['max_hp'] * (100 + $stats['def']),
            100
        );
    }
}
