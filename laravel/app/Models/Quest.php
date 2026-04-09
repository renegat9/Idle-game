<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quest extends Model
{
    protected $fillable = [
        'zone_id', 'type', 'title', 'description', 'steps_count',
        'order_index', 'reward_xp', 'reward_gold', 'reward_loot_rarity',
        'is_repeatable', 'is_ai_generated',
    ];

    protected $casts = ['is_repeatable' => 'boolean', 'is_ai_generated' => 'boolean'];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(QuestStep::class)->orderBy('step_index');
    }

    public function userQuests(): HasMany
    {
        return $this->hasMany(UserQuest::class);
    }
}
