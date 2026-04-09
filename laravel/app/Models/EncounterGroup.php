<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncounterGroup extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'zone_id', 'name', 'monster_ids', 'level_min', 'level_max', 'weight', 'is_boss_encounter',
    ];

    protected $casts = [
        'monster_ids' => 'array',
        'level_min' => 'integer',
        'level_max' => 'integer',
        'weight' => 'integer',
        'is_boss_encounter' => 'boolean',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
