<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_combat_status_effects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hero_id')->constrained('heroes')->onDelete('cascade');
            $table->string('effect_slug', 30); // etourdi|endormi|en_feu|empoisonne|ralenti|inspire|protege|regeneration|terrifie
            $table->unsignedTinyInteger('remaining_turns')->default(1);
            $table->tinyInteger('intensity_pct')->default(0); // INT buff accumulation (Philosophe), etc.
            $table->string('source', 20)->default('trait'); // trait|talent|item|monster|quest
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_combat_status_effects');
    }
};
