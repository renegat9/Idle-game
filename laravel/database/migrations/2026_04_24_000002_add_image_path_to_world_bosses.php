<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('world_bosses', function (Blueprint $table) {
            if (!Schema::hasColumn('world_bosses', 'image_path')) {
                $table->string('image_path')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('world_bosses', function (Blueprint $table) {
            $table->dropColumnIfExists('image_path');
        });
    }
};
