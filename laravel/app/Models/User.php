<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'username', 'email', 'password',
        'gold', 'level', 'xp', 'xp_to_next_level',
        'current_zone_id', 'last_idle_calc_at', 'narrator_frequency',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'gold' => 'integer',
        'level' => 'integer',
        'xp' => 'integer',
        'xp_to_next_level' => 'integer',
        'last_idle_calc_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function heroes()
    {
        return $this->hasMany(Hero::class);
    }

    public function activeHeroes()
    {
        return $this->hasMany(Hero::class)->where('is_active', true)->orderBy('slot_index');
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function currentZone()
    {
        return $this->belongsTo(Zone::class, 'current_zone_id');
    }

    public function activeExploration()
    {
        return $this->hasOne(UserExploration::class)->where('is_active', true);
    }

    public function zoneProgress()
    {
        return $this->hasMany(UserZoneProgress::class);
    }
}
