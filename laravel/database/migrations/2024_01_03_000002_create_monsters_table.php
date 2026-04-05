<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monsters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 50)->unique();
            $table->enum('monster_type', ['normal', 'mini_boss', 'boss'])->default('normal');
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedSmallInteger('base_hp');
            $table->unsignedSmallInteger('base_atq');
            $table->unsignedSmallInteger('base_def');
            $table->unsignedSmallInteger('base_vit');
            $table->unsignedSmallInteger('base_int')->default(0);
            $table->unsignedSmallInteger('base_cha')->default(0);
            $table->enum('element', ['physique', 'feu', 'glace', 'foudre', 'poison', 'sacre', 'ombre'])->default('physique');
            $table->unsignedSmallInteger('xp_reward');
            $table->unsignedSmallInteger('gold_min')->default(0);
            $table->unsignedSmallInteger('gold_max')->default(0);
            $table->unsignedSmallInteger('loot_bonus')->default(0);
            $table->json('behavior_data')->nullable();
            $table->json('phase2_data')->nullable();
            $table->boolean('is_active')->default(true);

            $table->index(['zone_id'], 'idx_monsters_zone');
            $table->index(['monster_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monsters');
    }
};
