<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('element_chart', function (Blueprint $table) {
            $table->id();
            $table->enum('attacker_element', ['physique', 'feu', 'glace', 'foudre', 'poison', 'sacre', 'ombre']);
            $table->enum('defender_element', ['physique', 'feu', 'glace', 'foudre', 'poison', 'sacre', 'ombre']);
            $table->unsignedSmallInteger('damage_multiplier')->default(100);
            $table->unique(['attacker_element', 'defender_element'], 'uk_element_matchup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('element_chart');
    }
};
