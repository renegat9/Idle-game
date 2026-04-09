<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_zone_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained();
            $table->unsignedInteger('total_combats')->default(0);
            $table->unsignedInteger('total_victories')->default(0);
            $table->boolean('boss_defeated')->default(false);
            $table->timestamp('unlocked_at')->useCurrent();

            $table->unique(['user_id', 'zone_id'], 'uk_user_zone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_zone_progress');
    }
};
