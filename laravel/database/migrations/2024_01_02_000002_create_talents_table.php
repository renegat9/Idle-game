<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes');
            $table->string('name', 100);
            $table->string('description', 255);
            $table->enum('branch', ['offensive', 'defensive', 'defaut']);
            $table->unsignedTinyInteger('tier')->default(1);
            $table->unsignedTinyInteger('position')->default(1);
            $table->unsignedTinyInteger('cost')->default(1);
            $table->unsignedTinyInteger('required_points_in_branch')->default(0);
            $table->enum('talent_type', ['passif', 'actif', 'reactif'])->default('passif');
            $table->json('effect_data');
            $table->unsignedBigInteger('prerequisite_talent_id')->nullable();

            $table->index(['class_id', 'branch'], 'idx_talents_class_branch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talents');
    }
};
