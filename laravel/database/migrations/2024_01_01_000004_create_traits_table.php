<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traits', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 30)->unique();
            $table->string('name', 50);
            $table->string('description', 255);
            $table->string('flavor_text', 255);
            $table->enum('trigger_moment', [
                'turn_start',
                'after_attack',
                'after_combat',
                'dungeon_entry',
                'permanent',
                'on_target_low_hp',
            ]);
            $table->unsignedTinyInteger('base_chance')->default(0);
            $table->unsignedTinyInteger('chance_level_26')->default(0);
            $table->unsignedTinyInteger('chance_level_51')->default(0);
            $table->unsignedTinyInteger('chance_level_76')->default(0);
            $table->json('effect_data');
            $table->json('scaling_data')->nullable();
            $table->string('out_of_combat_effect', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traits');
    }
};
