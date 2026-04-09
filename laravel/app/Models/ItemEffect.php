<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemEffect extends Model
{
    public $timestamps = false;

    protected $fillable = ['item_id', 'effect_key', 'description', 'effect_data'];

    protected $casts = ['effect_data' => 'array'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
