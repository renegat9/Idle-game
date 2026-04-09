<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->enum('rarity', ['commun', 'peu_commun', 'rare', 'epique', 'legendaire', 'wtf'])->default('commun');
            $table->enum('slot', ['arme', 'armure', 'casque', 'bottes', 'accessoire', 'truc_bizarre']);
            $table->enum('element', ['physique', 'feu', 'glace', 'foudre', 'poison', 'sacre', 'ombre'])->default('physique');
            $table->json('allowed_classes')->nullable();
            $table->smallInteger('base_atq')->default(0);
            $table->smallInteger('base_def')->default(0);
            $table->smallInteger('base_hp')->default(0);
            $table->smallInteger('base_vit')->default(0);
            $table->smallInteger('base_cha')->default(0);
            $table->smallInteger('base_int')->default(0);
            $table->unsignedSmallInteger('base_level')->default(1);
            $table->unsignedSmallInteger('base_sell_value')->default(5);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_templates');
    }
};
