<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Monster extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'zone_id', 'name', 'slug', 'monster_type', 'level',
        'base_hp', 'base_atq', 'base_def', 'base_vit', 'base_int', 'base_cha',
        'element', 'xp_reward', 'gold_min', 'gold_max', 'loot_bonus',
        'behavior_data', 'phase2_data', 'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'base_hp' => 'integer', 'base_atq' => 'integer', 'base_def' => 'integer',
        'base_vit' => 'integer', 'base_int' => 'integer', 'base_cha' => 'integer',
        'xp_reward' => 'integer', 'gold_min' => 'integer', 'gold_max' => 'integer',
        'loot_bonus' => 'integer',
        'behavior_data' => 'array',
        'phase2_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function skills()
    {
        return $this->hasMany(MonsterSkill::class);
    }

    public function toStatArray(): array
    {
        return [
            'hp' => $this->base_hp,
            'atq' => $this->base_atq,
            'def' => $this->base_def,
            'vit' => $this->base_vit,
            'int' => $this->base_int,
            'cha' => $this->base_cha,
            'max_hp' => $this->base_hp,
            'current_hp' => $this->base_hp,
            'element' => $this->element,
            'level' => $this->level,
            'name' => $this->name,
            'monster_id' => $this->id,
        ];
    }
}
