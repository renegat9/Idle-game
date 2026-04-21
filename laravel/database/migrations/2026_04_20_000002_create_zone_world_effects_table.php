<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Active world effects on a zone (M01-M10 from QUESTS_EFFECTS.md §4.4).
 * Scoped to a user so two players don't share the same zone effect state.
 * Permanent effects (M01, M02, M03, M07, M08, M09, M10) have expires_at = null.
 * Time-limited (M04 24h, M05 2h, M06 4h) have explicit timestamps.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zone_world_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('effect_id', 10); // M01-M10
            $table->string('name', 100);
            $table->json('data')->nullable(); // extra payload (merchant items, dungeon id…)
            $table->boolean('is_permanent')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['zone_id', 'user_id', 'effect_id']); // one active instance per effect per zone/user
            $table->index(['user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zone_world_effects');
    }
};
