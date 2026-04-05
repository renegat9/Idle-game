<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->integer('step_index'); // 1-based
            $table->json('content'); // {narration, narrator_comment, choices: [{id, text, test, success, failure}]}
            $table->timestamps();
            $table->unique(['quest_id', 'step_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_steps');
    }
};
