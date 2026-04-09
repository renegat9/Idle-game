<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monster_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monster_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('description', 255);
            $table->enum('skill_type', ['attaque', 'soin', 'buff', 'debuff', 'special'])->default('attaque');
            $table->unsignedSmallInteger('damage_percent')->default(100);
            $table->unsignedTinyInteger('cooldown_turns')->default(0);
            $table->unsignedTinyInteger('use_chance')->default(30);
            $table->json('effect_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monster_skills');
    }
};
