<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 30)->unique();
            $table->string('name', 50);
            $table->unsignedSmallInteger('base_hp');
            $table->unsignedSmallInteger('base_atq');
            $table->unsignedSmallInteger('base_def');
            $table->unsignedSmallInteger('base_vit');
            $table->unsignedSmallInteger('base_cha');
            $table->unsignedSmallInteger('base_int');
            $table->string('passive_bonus_description', 255);
            $table->string('passive_bonus_key', 50);
            $table->smallInteger('passive_bonus_value')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};
