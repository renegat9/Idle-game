<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserQuest extends Model
{
    protected $fillable = [
        'user_id', 'quest_id', 'status', 'current_step',
        'heroic_score', 'cunning_score', 'comic_score', 'cautious_score',
        'step_results', 'effects_active', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'step_results'   => 'array',
        'effects_active' => 'array',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
    ];

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
