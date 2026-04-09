<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tavern_recruits', function (Blueprint $table) {
            if (!Schema::hasColumn('tavern_recruits', 'is_legendary')) {
                $table->boolean('is_legendary')->default(false)->after('is_hired');
            }
            if (!Schema::hasColumn('tavern_recruits', 'legendary_epithet')) {
                $table->string('legendary_epithet', 100)->nullable()->after('is_legendary');
            }
            if (!Schema::hasColumn('tavern_recruits', 'legendary_backstory')) {
                $table->string('legendary_backstory', 300)->nullable()->after('legendary_epithet');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tavern_recruits', function (Blueprint $table) {
            $table->dropColumn(['is_legendary', 'legendary_epithet', 'legendary_backstory']);
        });
    }
};
