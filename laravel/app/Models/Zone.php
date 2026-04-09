<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'slug', 'name', 'description', 'level_min', 'level_max',
        'dominant_element', 'is_magical', 'unlock_requirement',
        'order_index', 'avg_combat_duration', 'ai_generated',
    ];

    protected $casts = [
        'level_min' => 'integer',
        'level_max' => 'integer',
        'is_magical' => 'boolean',
        'order_index' => 'integer',
        'avg_combat_duration' => 'integer',
        'ai_generated' => 'boolean',
    ];

    public function monsters()
    {
        return $this->hasMany(Monster::class);
    }

    public function encounterGroups()
    {
        return $this->hasMany(EncounterGroup::class);
    }

    public function itemTemplates()
    {
        return $this->hasMany(ItemTemplate::class);
    }
}
