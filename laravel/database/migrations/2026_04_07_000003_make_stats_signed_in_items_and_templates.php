<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Passe les colonnes de stats en SMALLINT signé pour autoriser les valeurs négatives
 * (malus de VIT sur armures lourdes, malus de CHA sur certains objets maudits, etc.)
 *
 * Colonnes concernées : base_atq, base_def, base_hp, base_vit, base_cha, base_int (item_templates)
 *                       atq, def, hp, vit, cha, int (items)
 */
return new class extends Migration
{
    public function up(): void
    {
        // item_templates : stats de base signées
        Schema::table('item_templates', function (Blueprint $table) {
            $table->smallInteger('base_atq')->default(0)->change();
            $table->smallInteger('base_def')->default(0)->change();
            $table->smallInteger('base_hp')->default(0)->change();
            $table->smallInteger('base_vit')->default(0)->change();
            $table->smallInteger('base_cha')->default(0)->change();
            $table->smallInteger('base_int')->default(0)->change();
        });

        // items : stats signées (pour items avec malus via enchantements maudits ou templates)
        Schema::table('items', function (Blueprint $table) {
            $table->smallInteger('atq')->default(0)->change();
            $table->smallInteger('def')->default(0)->change();
            $table->smallInteger('hp')->default(0)->change();
            $table->smallInteger('vit')->default(0)->change();
            $table->smallInteger('cha')->default(0)->change();
            $table->smallInteger('int')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('item_templates', function (Blueprint $table) {
            $table->unsignedSmallInteger('base_atq')->default(0)->change();
            $table->unsignedSmallInteger('base_def')->default(0)->change();
            $table->unsignedSmallInteger('base_hp')->default(0)->change();
            $table->unsignedSmallInteger('base_vit')->default(0)->change();
            $table->unsignedSmallInteger('base_cha')->default(0)->change();
            $table->unsignedSmallInteger('base_int')->default(0)->change();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->unsignedSmallInteger('atq')->default(0)->change();
            $table->unsignedSmallInteger('def')->default(0)->change();
            $table->unsignedSmallInteger('hp')->default(0)->change();
            $table->unsignedSmallInteger('vit')->default(0)->change();
            $table->unsignedSmallInteger('cha')->default(0)->change();
            $table->unsignedSmallInteger('int')->default(0)->change();
        });
    }
};
