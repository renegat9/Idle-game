<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TavernRecruit extends Model
{
    protected $fillable = [
        'user_id', 'race_id', 'class_id', 'trait_id',
        'name', 'hire_cost', 'is_hired', 'expires_at',
        'is_legendary', 'legendary_epithet', 'legendary_backstory',
    ];

    protected $casts = [
        'is_hired'     => 'boolean',
        'is_legendary' => 'boolean',
        'expires_at'   => 'datetime',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function gameClass(): BelongsTo
    {
        return $this->belongsTo(GameClass::class, 'class_id');
    }

    public function trait_(): BelongsTo
    {
        return $this->belongsTo(Trait_::class, 'trait_id');
    }
}
