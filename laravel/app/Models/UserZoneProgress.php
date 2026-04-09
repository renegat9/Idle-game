<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserZoneProgress extends Model
{
    public $timestamps = false;

    protected $table = 'user_zone_progress';

    protected $fillable = ['user_id', 'zone_id', 'total_combats', 'total_victories', 'boss_defeated', 'unlocked_at'];

    protected $casts = [
        'total_combats' => 'integer',
        'total_victories' => 'integer',
        'boss_defeated' => 'boolean',
        'unlocked_at' => 'datetime',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
