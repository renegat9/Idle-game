<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catalogue de référence des consommables
        Schema::create('consumables', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->string('flavor_text', 255)->nullable();
            // Effet : heal_hp | restore_hp_pct | xp_boost | gold_boost | atq_boost | def_boost | cure_debuff
            $table->string('effect_type', 30);
            $table->unsignedSmallInteger('effect_value')->default(0); // montant ou % selon effect_type
            $table->unsignedTinyInteger('duration_turns')->default(0); // 0 = instantané
            $table->enum('rarity', ['commun', 'peu_commun', 'rare'])->default('commun');
            $table->unsignedSmallInteger('buy_price')->default(10);
            $table->unsignedSmallInteger('sell_value')->default(5);
            $table->unsignedTinyInteger('stack_max')->default(99);
        });

        // Inventaire consommables du joueur
        Schema::create('user_consumables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('consumable_slug', 50);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->timestamp('obtained_at')->useCurrent();

            $table->unique(['user_id', 'consumable_slug'], 'idx_user_consumable_unique');
            $table->index('user_id', 'idx_user_consumables_user');

            $table->foreign('consumable_slug')->references('slug')->on('consumables')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_consumables');
        Schema::dropIfExists('consumables');
    }
};
