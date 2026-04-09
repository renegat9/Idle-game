<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('is_generic')->default(false);
            $table->unsignedTinyInteger('drop_chance')->default(20);
            $table->unsignedSmallInteger('base_value')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
