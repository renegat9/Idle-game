<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'equipped_by_hero_id', 'template_id',
        'name', 'description', 'rarity', 'slot', 'element',
        'item_level', 'atq', 'def', 'hp', 'vit', 'cha', 'int',
        'sell_value', 'is_ai_generated',
        'durability_current', 'durability_max', 'enchant_count',
    ];

    protected $casts = [
        'item_level' => 'integer',
        'atq' => 'integer', 'def' => 'integer', 'hp' => 'integer',
        'vit' => 'integer', 'cha' => 'integer', 'int' => 'integer',
        'sell_value' => 'integer',
        'is_ai_generated' => 'boolean',
        'durability_current' => 'integer',
        'durability_max' => 'integer',
        'enchant_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function equippedByHero()
    {
        return $this->belongsTo(Hero::class, 'equipped_by_hero_id');
    }

    public function effects()
    {
        return $this->hasMany(ItemEffect::class);
    }
}
