<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BossContribution extends Model
{
    protected $fillable = [
        'boss_id',
        'user_id',
        'damage_dealt',
        'hits_count',
        'reward_claimed',
    ];

    protected $casts = [
        'boss_id'        => 'integer',
        'user_id'        => 'integer',
        'damage_dealt'   => 'integer',
        'hits_count'     => 'integer',
        'reward_claimed' => 'boolean',
    ];

    public function boss()
    {
        return $this->belongsTo(WorldBoss::class, 'boss_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
