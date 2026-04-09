<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounter_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100)->nullable();
            $table->json('monster_ids');
            $table->unsignedTinyInteger('level_min')->default(1);
            $table->unsignedTinyInteger('level_max')->default(5);
            $table->unsignedSmallInteger('weight')->default(100);
            $table->boolean('is_boss_encounter')->default(false);

            $table->index(['zone_id', 'level_min', 'level_max'], 'idx_encounter_zone_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounter_groups');
    }
};
