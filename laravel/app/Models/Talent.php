<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Talent extends Model
{
    protected $table = 'talents';

    public $timestamps = false;

    protected $fillable = [
        'class_id', 'name', 'description', 'branch', 'tier', 'position',
        'cost', 'required_points_in_branch', 'talent_type', 'effect_data', 'prerequisite_talent_id',
    ];

    protected $casts = [
        'tier' => 'integer',
        'position' => 'integer',
        'cost' => 'integer',
        'required_points_in_branch' => 'integer',
        'effect_data' => 'array',
    ];

    public function gameClass()
    {
        return $this->belongsTo(GameClass::class, 'class_id');
    }
}
