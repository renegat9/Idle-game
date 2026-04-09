<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('narrator_cache', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 50);
            $table->string('context_hash', 32);
            $table->text('text');
            $table->boolean('is_ai_generated')->default(false);
            $table->unsignedInteger('usage_count')->default(1);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_type', 'context_hash'], 'idx_narrator_event_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('narrator_cache');
    }
};
