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
        Schema::create('user_limit_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('limit_code', 50);
            $table->integer('usage_count')->default(0);
            $table->timestamp('period_start');                  // günlükse o günün başlangıcı
            $table->timestamp('last_usage_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'limit_code', 'period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_limit_usages');
    }
};
