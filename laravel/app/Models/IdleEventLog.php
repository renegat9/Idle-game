<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdleEventLog extends Model
{
    public $timestamps = false;

    protected $table = 'idle_event_log';

    protected $fillable = ['user_id', 'event_type', 'narrator_text', 'event_data', 'is_read'];

    protected $casts = [
        'event_data' => 'array',
        'is_read' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
