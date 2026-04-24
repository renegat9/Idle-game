<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds time-based expiry to hero_buffs.
 * Required for EQ01 (hero absent for X minutes, returns automatically).
 * When null: buff expires only via remaining_combats decrement.
 * When set: whichever comes first (time or combat count) removes the buff.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_buffs', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('remaining_combats');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('hero_buffs', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
            $table->dropColumn('expires_at');
        });
    }
};
