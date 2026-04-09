<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->enum('rarity', ['commun', 'peu_commun', 'rare', 'epique', 'legendaire', 'wtf']);
            $table->enum('slot', ['arme', 'armure', 'casque', 'bottes', 'accessoire', 'truc_bizarre']);
            $table->unsignedTinyInteger('item_level')->default(1);
            $table->integer('atq')->default(0);
            $table->integer('def')->default(0);
            $table->integer('hp')->default(0);
            $table->integer('vit')->default(0);
            $table->integer('cha')->default(0);
            $table->integer('int')->default(0);
            $table->integer('sell_value')->default(0);
            $table->integer('shop_price')->default(0);
            $table->boolean('is_sold')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['zone_id', 'user_id', 'is_active'], 'idx_shop_zone_user_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_inventories');
    }
};
