<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_buffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hero_id')->constrained()->cascadeOnDelete();
            $table->string('buff_key', 50);
            $table->string('name', 100);
            $table->boolean('is_buff')->default(true);
            $table->integer('value')->default(0);
            $table->string('stat_affected', 20)->nullable();
            $table->unsignedTinyInteger('remaining_combats')->nullable();
            $table->string('source', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['hero_id'], 'idx_hero_buffs_hero');
            $table->index(['remaining_combats']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_buffs');
    }
};
