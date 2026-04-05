<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('equipped_by_hero_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->enum('rarity', ['commun', 'peu_commun', 'rare', 'epique', 'legendaire', 'wtf'])->default('commun');
            $table->enum('slot', ['arme', 'armure', 'casque', 'bottes', 'accessoire', 'truc_bizarre']);
            $table->enum('element', ['physique', 'feu', 'glace', 'foudre', 'poison', 'sacre', 'ombre'])->default('physique');
            $table->unsignedTinyInteger('item_level')->default(1);
            $table->unsignedSmallInteger('atq')->default(0);
            $table->unsignedSmallInteger('def')->default(0);
            $table->unsignedSmallInteger('hp')->default(0);
            $table->unsignedSmallInteger('vit')->default(0);
            $table->unsignedSmallInteger('cha')->default(0);
            $table->unsignedSmallInteger('int')->default(0);
            $table->unsignedSmallInteger('sell_value')->default(5);
            $table->boolean('is_ai_generated')->default(false);
            $table->timestamp('obtained_at')->useCurrent();

            $table->index(['user_id'], 'idx_items_user');
            $table->index(['equipped_by_hero_id'], 'idx_items_equipped');
            $table->index(['rarity']);

            $table->foreign('equipped_by_hero_id')->references('id')->on('heroes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
