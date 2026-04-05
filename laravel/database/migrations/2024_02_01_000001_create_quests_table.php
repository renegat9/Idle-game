<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->enum('type', ['zone', 'daily', 'wtf', 'event'])->default('zone');
            $table->string('title');
            $table->text('description');
            $table->integer('steps_count');
            $table->integer('order_index')->default(0); // for zone quests: 1-10
            $table->integer('reward_xp')->default(0);
            $table->integer('reward_gold')->default(0);
            $table->string('reward_loot_rarity')->nullable(); // null = no loot
            $table->boolean('is_repeatable')->default(false);
            $table->boolean('is_ai_generated')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quests');
    }
};
