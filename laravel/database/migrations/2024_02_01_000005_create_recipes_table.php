<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->json('ingredients'); // [{material_slug, quantity}]
            $table->integer('gold_cost')->default(0);
            $table->string('result_type')->default('item'); // item | consumable
            $table->string('result_slot')->nullable();       // arme | armure | etc.
            $table->string('result_rarity')->default('commun');
            $table->integer('result_level')->default(1);
            $table->json('result_stats')->nullable();        // fixed stats override
            $table->string('result_name');                   // pre-set name
            $table->text('result_description')->nullable();
            $table->boolean('is_discoverable')->default(true);
            $table->foreignId('unlock_zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
