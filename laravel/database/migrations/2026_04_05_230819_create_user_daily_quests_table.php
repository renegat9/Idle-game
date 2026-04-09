<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_daily_quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quest_id')->constrained('quests');
            $table->date('date');
            $table->enum('status', ['available', 'in_progress', 'completed'])->default('available');

            $table->index(['user_id', 'date'], 'idx_daily_quests_user_date');
        });
    }
    public function down(): void { Schema::dropIfExists('user_daily_quests'); }
};
