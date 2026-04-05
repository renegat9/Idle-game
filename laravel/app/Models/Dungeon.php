<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dungeon extends Model
{
    protected $fillable = [
        'user_id',
        'zone_id',
        'status',
        'current_room',
        'total_rooms',
        'rooms',
        'loot_gained',
        'gold_gained',
        'started_at',
        'completed_at',
        'available_at',
    ];

    protected $casts = [
        'current_room'  => 'integer',
        'total_rooms'   => 'integer',
        'rooms'         => 'array',
        'loot_gained'   => 'array',
        'gold_gained'   => 'integer',
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
        'available_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['completed', 'failed', 'abandoned']);
    }

    /**
     * Returns the current room data array (1-indexed).
     */
    public function currentRoomData(): ?array
    {
        $rooms = $this->rooms ?? [];
        $index = $this->current_room - 1;

        return $rooms[$index] ?? null;
    }
}
