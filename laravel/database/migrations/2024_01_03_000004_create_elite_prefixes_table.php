<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elite_prefixes', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 30)->unique();
            $table->string('name', 50);
            $table->unsignedSmallInteger('hp_multiplier')->default(100);
            $table->unsignedSmallInteger('atq_multiplier')->default(100);
            $table->unsignedSmallInteger('def_multiplier')->default(100);
            $table->unsignedSmallInteger('xp_multiplier')->default(150);
            $table->unsignedSmallInteger('gold_multiplier')->default(150);
            $table->unsignedSmallInteger('loot_multiplier')->default(150);
            $table->json('effect_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elite_prefixes');
    }
};
