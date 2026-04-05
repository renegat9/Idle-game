<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_buffs', function (Blueprint $table) {
            $table->boolean('is_debuff')->default(false)->after('is_buff');
            $table->integer('modifier_percent')->default(0)->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('hero_buffs', function (Blueprint $table) {
            $table->dropColumn(['is_debuff', 'modifier_percent']);
        });
    }
};
