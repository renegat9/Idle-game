<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trait_ extends Model
{
    protected $table = 'traits';
    public $timestamps = false;

    protected $fillable = [
        'slug', 'name', 'description', 'flavor_text',
        'trigger_moment', 'base_chance',
        'chance_level_26', 'chance_level_51', 'chance_level_76',
        'effect_data', 'scaling_data', 'out_of_combat_effect',
    ];

    protected $casts = [
        'base_chance' => 'integer',
        'chance_level_26' => 'integer',
        'chance_level_51' => 'integer',
        'chance_level_76' => 'integer',
        'effect_data' => 'array',
        'scaling_data' => 'array',
    ];

    public function heroes()
    {
        return $this->hasMany(Hero::class, 'trait_id');
    }
}
