<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combat_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->enum('combat_type', ['idle', 'dungeon', 'quest', 'world_boss'])->default('idle');
            $table->enum('result', ['victory', 'defeat', 'draw']);
            $table->unsignedTinyInteger('turns')->default(0);
            $table->unsignedSmallInteger('xp_gained')->default(0);
            $table->unsignedSmallInteger('gold_gained')->default(0);
            $table->json('loot_gained')->nullable();
            $table->json('trait_triggers')->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['user_id', 'occurred_at'], 'idx_combat_log_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combat_log');
    }
};
