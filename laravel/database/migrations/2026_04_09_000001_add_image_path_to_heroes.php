<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('heroes', function (Blueprint $table) {
            if (!Schema::hasColumn('heroes', 'image_path')) {
                $table->string('image_path')->nullable()->after('deaths');
            }
        });
    }

    public function down(): void
    {
        Schema::table('heroes', function (Blueprint $table) {
            if (Schema::hasColumn('heroes', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
