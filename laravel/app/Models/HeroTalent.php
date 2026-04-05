<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroTalent extends Model
{
    protected $table = 'hero_talents';

    public $timestamps = false;

    protected $fillable = ['hero_id', 'talent_id', 'unlocked_at'];

    public function hero()
    {
        return $this->belongsTo(Hero::class);
    }

    public function talent()
    {
        return $this->belongsTo(Talent::class);
    }
}
