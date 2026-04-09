<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('economy_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('transaction_type', ['gain', 'depense']);
            $table->string('source', 50);
            $table->integer('amount');
            $table->unsignedInteger('balance_after');
            $table->string('description', 255)->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['user_id', 'occurred_at'], 'idx_economy_user_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('economy_log');
    }
};
