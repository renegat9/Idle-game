<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idle_event_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 50);
            $table->string('narrator_text', 500)->nullable();
            $table->json('event_data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['user_id', 'is_read'], 'idx_idle_log_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idle_event_log');
    }
};
