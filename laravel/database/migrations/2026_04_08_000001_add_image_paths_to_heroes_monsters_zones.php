<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('heroes', function (Blueprint $table) {
            $table->string('image_path', 255)->nullable()->after('talent_reset_count');
        });

        Schema::table('monsters', function (Blueprint $table) {
            $table->string('image_path', 255)->nullable()->after('slug');
            $table->string('elite_image_path', 255)->nullable()->after('image_path');
        });

        Schema::table('zones', function (Blueprint $table) {
            $table->string('background_image_path', 255)->nullable()->after('dominant_element');
        });
    }

    public function down(): void
    {
        Schema::table('heroes', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
        Schema::table('monsters', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'elite_image_path']);
        });
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('background_image_path');
        });
    }
};
