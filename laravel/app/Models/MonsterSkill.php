<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonsterSkill extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'monster_id', 'name', 'description', 'skill_type',
        'damage_percent', 'cooldown_turns', 'use_chance', 'effect_data',
    ];

    protected $casts = [
        'damage_percent' => 'integer',
        'cooldown_turns' => 'integer',
        'use_chance' => 'integer',
        'effect_data' => 'array',
    ];

    public function monster()
    {
        return $this->belongsTo(Monster::class);
    }
}
