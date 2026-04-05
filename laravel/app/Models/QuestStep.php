<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestStep extends Model
{
    protected $fillable = ['quest_id', 'step_index', 'content'];

    protected $casts = ['content' => 'array'];

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }
}
