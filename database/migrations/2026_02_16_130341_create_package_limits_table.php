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
        Schema::create('package_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('package_definitions')->cascadeOnDelete();
            $table->string('limit_code', 50);                  // daily_message, daily_like, daily_super_like
            $table->string('limit_name', 100);
            $table->integer('limit_value');                     // -1 = sınırsız, 0 = kapalı, 5 = günlük 5
            $table->string('period', 20)->nullable();           // daily / weekly / monthly / total
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['package_id', 'limit_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_limits');
    }
};
