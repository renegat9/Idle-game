<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_talents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hero_id')->constrained()->cascadeOnDelete();
            $table->foreignId('talent_id')->constrained('talents');
            $table->timestamp('unlocked_at')->useCurrent();

            $table->unique(['hero_id', 'talent_id'], 'uk_hero_talent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_talents');
    }
};
