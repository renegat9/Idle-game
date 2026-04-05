<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipe extends Model
{
    protected $fillable = [
        'name', 'description', 'ingredients', 'gold_cost',
        'result_type', 'result_slot', 'result_rarity', 'result_level',
        'result_stats', 'result_name', 'result_description',
        'is_discoverable', 'unlock_zone_id',
    ];

    protected $casts = [
        'ingredients'  => 'array',
        'result_stats' => 'array',
        'is_discoverable' => 'boolean',
    ];

    public function unlockZone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'unlock_zone_id');
    }
}
