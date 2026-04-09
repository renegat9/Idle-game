<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heroes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('race_id')->constrained('races');
            $table->foreignId('class_id')->constrained('classes');
            $table->foreignId('trait_id')->constrained('traits');
            $table->string('name', 50);
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedInteger('xp_to_next_level')->default(100);
            $table->unsignedSmallInteger('current_hp');
            $table->unsignedSmallInteger('max_hp');
            $table->unsignedTinyInteger('talent_points')->default(0);
            $table->unsignedTinyInteger('slot_index')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('deaths')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'is_active'], 'idx_heroes_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heroes');
    }
};
