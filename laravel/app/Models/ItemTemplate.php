<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemTemplate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'zone_id', 'name', 'description', 'rarity', 'slot', 'element',
        'allowed_classes', 'base_atq', 'base_def', 'base_hp',
        'base_vit', 'base_cha', 'base_int', 'base_level', 'base_sell_value',
    ];

    protected $casts = [
        'allowed_classes' => 'array',
        'base_atq' => 'integer', 'base_def' => 'integer', 'base_hp' => 'integer',
        'base_vit' => 'integer', 'base_cha' => 'integer', 'base_int' => 'integer',
        'base_level' => 'integer', 'base_sell_value' => 'integer',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
