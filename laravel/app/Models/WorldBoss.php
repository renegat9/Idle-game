<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldBoss extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'total_hp',
        'current_hp',
        'status',
        'special_mechanic',
        'description',
        'image_path',
        'spawned_at',
        'defeated_at',
    ];

    protected $casts = [
        'total_hp'    => 'integer',
        'current_hp'  => 'integer',
        'spawned_at'  => 'datetime',
        'defeated_at' => 'datetime',
    ];

    public function contributions()
    {
        return $this->hasMany(BossContribution::class, 'boss_id');
    }
}
