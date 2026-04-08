<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ai_generation_log MODIFY COLUMN type ENUM(
            'narration',
            'loot_text',
            'loot_image',
            'quest',
            'music',
            'boss',
            'zone',
            'legendary_hero',
            'hero_image',
            'monster_image',
            'elite_monster',
            'zone_bg'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ai_generation_log MODIFY COLUMN type ENUM(
            'narration',
            'loot_text',
            'loot_image',
            'quest',
            'music',
            'boss',
            'zone'
        ) NOT NULL");
    }
};
