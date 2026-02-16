<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_match_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('matched_user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('score', 5, 4);                    // 0.0000 - 1.0000 arası puan
            $table->text('reason')->nullable();                 // eşleşme sebebi açıklaması
            $table->timestamps();

            $table->unique(['user_id', 'matched_user_id']);
            $table->index('score');                            // yüksek puanlıları hızlı çekmek için
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_match_scores');
    }
};
