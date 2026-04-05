<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50)->unique();
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->unsignedInteger('gold')->default(100);
            $table->unsignedTinyInteger('level')->default(1);
            $table->unsignedInteger('xp')->default(0);
            $table->unsignedInteger('xp_to_next_level')->default(100);
            $table->unsignedBigInteger('current_zone_id')->nullable();
            $table->timestamp('last_idle_calc_at')->nullable();
            $table->enum('narrator_frequency', ['bavard', 'normal', 'discret', 'muet'])->default('normal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
