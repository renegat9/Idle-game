<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'slug', 'name', 'base_hp', 'base_atq', 'base_def',
        'base_vit', 'base_cha', 'base_int',
        'passive_bonus_description', 'passive_bonus_key', 'passive_bonus_value',
    ];

    protected $casts = [
        'base_hp' => 'integer', 'base_atq' => 'integer', 'base_def' => 'integer',
        'base_vit' => 'integer', 'base_cha' => 'integer', 'base_int' => 'integer',
        'passive_bonus_value' => 'integer',
    ];

    public function heroes()
    {
        return $this->hasMany(Hero::class);
    }
}
