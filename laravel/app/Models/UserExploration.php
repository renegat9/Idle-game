<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserExploration extends Model
{
    public $timestamps = false;

    protected $table = 'user_exploration';

    protected $fillable = ['user_id', 'zone_id', 'is_active', 'started_at', 'last_collected_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'last_collected_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
