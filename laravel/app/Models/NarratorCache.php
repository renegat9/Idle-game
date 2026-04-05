<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NarratorCache extends Model
{
    public $timestamps = false;

    protected $table = 'narrator_cache';

    protected $fillable = ['event_type', 'context_hash', 'text', 'is_ai_generated', 'usage_count'];

    protected $casts = [
        'is_ai_generated' => 'boolean',
        'usage_count' => 'integer',
    ];
}
