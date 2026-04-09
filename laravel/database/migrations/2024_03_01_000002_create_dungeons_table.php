<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dungeons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->enum('status', ['active', 'completed', 'failed', 'abandoned'])->default('active');
            $table->unsignedTinyInteger('current_room')->default(1);
            $table->unsignedTinyInteger('total_rooms');
            $table->json('rooms');
            $table->json('loot_gained')->nullable();
            $table->unsignedInteger('gold_gained')->default(0);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('available_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dungeons');
    }
};
