<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 30)->unique();
            $table->string('name', 50);
            $table->string('role', 30);
            $table->string('key_skill_name', 100);
            $table->string('key_skill_description', 255);
            $table->smallInteger('mod_hp')->default(0);
            $table->smallInteger('mod_atq')->default(0);
            $table->smallInteger('mod_def')->default(0);
            $table->smallInteger('mod_vit')->default(0);
            $table->smallInteger('mod_cha')->default(0);
            $table->smallInteger('mod_int')->default(0);
            $table->json('primary_stats');
            $table->json('weapon_types');
            $table->json('armor_types');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
