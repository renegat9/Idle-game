<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_inventories', function (Blueprint $table) {
            $table->string('effect_key', 10)->nullable()->after('sell_value');
            $table->string('effect_description', 255)->nullable()->after('effect_key');
            $table->json('effect_data')->nullable()->after('effect_description');
        });
    }

    public function down(): void
    {
        Schema::table('shop_inventories', function (Blueprint $table) {
            $table->dropColumn(['effect_key', 'effect_description', 'effect_data']);
        });
    }
};
