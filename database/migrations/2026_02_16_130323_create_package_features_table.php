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
        Schema::create('package_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('package_definitions')->cascadeOnDelete();
            $table->string('feature_code', 50);                // ad_free, unlimited_msg, gallery_limit
            $table->string('feature_name', 100);               // Reklamsız Kullanım, Sınırsız Mesajlaşma
            $table->string('value', 50);                       // true / false / 10 / 50 / unlimited
            $table->string('value_type', 20);                  // boolean / number / text
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['package_id', 'feature_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_features');
    }
};
