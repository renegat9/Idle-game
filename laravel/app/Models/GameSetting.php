<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSetting extends Model
{
    public $timestamps = false;

    protected $fillable = ['setting_key', 'setting_value', 'description'];

    protected $casts = [
        'setting_value' => 'integer',
    ];
}
