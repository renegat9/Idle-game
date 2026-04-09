<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute les colonnes nécessaires à l'enchantement :
 * - items.durability_current / durability_max
 * - items.enchant_count
 * - item_effects.is_enchantment
 */
return new class extends Migration
{
    public function up(): void
    {
        // Colonnes durabilité + compteur enchantements sur items
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'durability_current')) {
                $table->unsignedSmallInteger('durability_current')->default(100)->after('sell_value');
            }
            if (!Schema::hasColumn('items', 'durability_max')) {
                $table->unsignedSmallInteger('durability_max')->default(100)->after('durability_current');
            }
            if (!Schema::hasColumn('items', 'enchant_count')) {
                $table->unsignedTinyInteger('enchant_count')->default(0)->after('durability_max');
            }
        });

        // Colonne is_enchantment sur item_effects
        Schema::table('item_effects', function (Blueprint $table) {
            if (!Schema::hasColumn('item_effects', 'is_enchantment')) {
                $table->boolean('is_enchantment')->default(false)->after('effect_data');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumnIfExists('durability_current');
            $table->dropColumnIfExists('durability_max');
            $table->dropColumnIfExists('enchant_count');
        });

        Schema::table('item_effects', function (Blueprint $table) {
            $table->dropColumnIfExists('is_enchantment');
        });
    }
};
