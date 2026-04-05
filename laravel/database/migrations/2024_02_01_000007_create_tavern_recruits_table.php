<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tavern_recruits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('race_id')->constrained('races');
            $table->foreignId('class_id')->constrained('classes');
            $table->foreignId('trait_id')->constrained('traits');
            $table->string('name');
            $table->integer('hire_cost');
            $table->boolean('is_hired')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->index(['user_id', 'is_hired', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tavern_recruits');
    }
};
