<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loot_image_pool', function (Blueprint $table) {
            $table->id();
            $table->string('slot', 30);
            $table->string('rarity', 30);
            $table->string('image_url', 255);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['slot', 'rarity'], 'idx_pool_slot_rarity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loot_image_pool');
    }
};
