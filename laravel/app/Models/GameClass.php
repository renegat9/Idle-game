<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameClass extends Model
{
    protected $table = 'classes';
    public $timestamps = false;

    protected $fillable = [
        'slug', 'name', 'role', 'key_skill_name', 'key_skill_description',
        'mod_hp', 'mod_atq', 'mod_def', 'mod_vit', 'mod_cha', 'mod_int',
        'primary_stats', 'weapon_types', 'armor_types',
    ];

    protected $casts = [
        'mod_hp' => 'integer', 'mod_atq' => 'integer', 'mod_def' => 'integer',
        'mod_vit' => 'integer', 'mod_cha' => 'integer', 'mod_int' => 'integer',
        'primary_stats' => 'array',
        'weapon_types' => 'array',
        'armor_types' => 'array',
    ];

    public function heroes()
    {
        return $this->hasMany(Hero::class, 'class_id');
    }

    public function talents()
    {
        return $this->hasMany(Talent::class, 'class_id');
    }
}
