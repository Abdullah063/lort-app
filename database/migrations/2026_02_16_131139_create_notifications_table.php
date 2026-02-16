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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 30);                        // welcome / package / receipt / match
            $table->string('title', 200);
            $table->text('body')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('email_sent')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read']);             // okunmamış bildirimleri hızlı çekmek için
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
