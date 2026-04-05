<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroBuff extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'hero_id', 'buff_key', 'name', 'is_buff', 'value',
        'stat_affected', 'remaining_combats', 'source',
    ];

    protected $casts = [
        'is_buff' => 'boolean',
        'value' => 'integer',
        'remaining_combats' => 'integer',
    ];

    public function hero()
    {
        return $this->belongsTo(Hero::class);
    }
}
