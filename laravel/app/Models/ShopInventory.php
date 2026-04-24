<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopInventory extends Model
{
    protected $fillable = [
        'zone_id',
        'user_id',
        'name',
        'rarity',
        'slot',
        'item_level',
        'atq',
        'def',
        'hp',
        'vit',
        'cha',
        'int',
        'sell_value',
        'effect_key',
        'effect_description',
        'effect_data',
        'shop_price',
        'is_sold',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'item_level'  => 'integer',
        'atq'         => 'integer',
        'def'         => 'integer',
        'hp'          => 'integer',
        'vit'         => 'integer',
        'cha'         => 'integer',
        'int'         => 'integer',
        'sell_value'  => 'integer',
        'shop_price'  => 'integer',
        'is_sold'     => 'boolean',
        'is_active'   => 'boolean',
        'expires_at'  => 'datetime',
        'effect_data' => 'array',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
