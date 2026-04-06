<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasonal_events', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('flavor_text', 255)->nullable();

            // Date range (month-day format, year-independent for recurring events)
            $table->unsignedTinyInteger('start_month');
            $table->unsignedTinyInteger('start_day');
            $table->unsignedTinyInteger('end_month');
            $table->unsignedTinyInteger('end_day');

            // Gameplay modifiers (all percentages, integer only)
            $table->smallInteger('xp_bonus_pct')->default(0);      // e.g. 25 = +25% XP
            $table->smallInteger('gold_bonus_pct')->default(0);     // e.g. 20 = +20% gold
            $table->smallInteger('loot_bonus_pct')->default(0);     // e.g. 15 = +15% loot chance
            $table->smallInteger('rare_loot_bonus_pct')->default(0);// e.g. 10 = +10% rare+ chance

            // Special quest type unlocked during event ('wtf', 'event', null)
            $table->string('quest_type_unlock', 20)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['start_month', 'start_day'], 'idx_seasonal_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasonal_events');
    }
};
