<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_exploration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained();
            $table->boolean('is_active')->default(true);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('last_collected_at')->useCurrent();

            $table->index(['user_id', 'is_active'], 'idx_exploration_user_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_exploration');
    }
};
