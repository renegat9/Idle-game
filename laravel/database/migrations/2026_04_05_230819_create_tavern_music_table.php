<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tavern_music', function (Blueprint $table) {
            $table->id();
            $table->string('style', 50)->comment('victoire_epique, defaite, exploration, etc.');
            $table->text('prompt_used');
            $table->string('file_path', 255);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('play_count')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('style', 'idx_music_style');
        });
    }
    public function down(): void { Schema::dropIfExists('tavern_music'); }
};
