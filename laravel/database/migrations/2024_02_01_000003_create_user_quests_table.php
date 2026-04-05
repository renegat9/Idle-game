<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->enum('status', ['available', 'in_progress', 'completed', 'failed'])->default('available');
            $table->integer('current_step')->default(1);
            $table->integer('heroic_score')->default(0);
            $table->integer('cunning_score')->default(0);
            $table->integer('comic_score')->default(0);
            $table->integer('cautious_score')->default(0);
            $table->json('step_results')->nullable(); // array of {step, choice, success, effects}
            $table->json('effects_active')->nullable(); // pending effects to apply
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'quest_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_quests');
    }
};
