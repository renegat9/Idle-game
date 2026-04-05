<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_generation_log', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['narration', 'loot_text', 'loot_image', 'quest', 'music', 'boss', 'zone']);
            $table->string('prompt_summary', 255)->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->unsignedInteger('cost_estimate')->nullable()->comment('En micro-centimes');
            $table->boolean('success')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['type', 'created_at'], 'idx_ai_log_type_date');
        });
    }
    public function down(): void { Schema::dropIfExists('ai_generation_log'); }
};
