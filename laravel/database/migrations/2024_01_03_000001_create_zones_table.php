<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 30)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('level_min')->default(1);
            $table->unsignedTinyInteger('level_max')->default(5);
            $table->enum('dominant_element', ['physique', 'feu', 'glace', 'foudre', 'poison', 'sacre', 'ombre'])->default('physique');
            $table->boolean('is_magical')->default(false);
            $table->string('unlock_requirement', 255)->nullable();
            $table->unsignedTinyInteger('order_index')->default(1);
            $table->unsignedSmallInteger('avg_combat_duration')->default(60);
            $table->boolean('ai_generated')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
