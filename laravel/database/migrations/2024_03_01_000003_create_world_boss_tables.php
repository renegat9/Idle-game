<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('world_bosses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 100)->unique();
            $table->unsignedInteger('total_hp');
            $table->unsignedInteger('current_hp');
            $table->enum('status', ['inactive', 'active', 'defeated'])->default('inactive');
            $table->string('special_mechanic', 100)->nullable();
            $table->timestamp('spawned_at')->nullable();
            $table->timestamp('defeated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('boss_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boss_id')->constrained('world_bosses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('damage_dealt')->default(0);
            $table->unsignedInteger('hits_count')->default(0);
            $table->boolean('reward_claimed')->default(false);
            $table->unique(['boss_id', 'user_id']);
            $table->index(['boss_id', 'damage_dealt']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boss_contributions');
        Schema::dropIfExists('world_bosses');
    }
};
