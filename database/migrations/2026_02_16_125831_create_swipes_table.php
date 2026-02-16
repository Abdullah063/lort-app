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
        Schema::create('swipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('swiper_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('swiped_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20);            // like / nope / super_like
            $table->timestamps();

            $table->unique(['swiper_id', 'swiped_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swipes');
    }
};
