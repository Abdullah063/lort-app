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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('business_name', 200);
            $table->string('position', 100)->nullable();
            $table->string('sector', 100)->nullable();       // finans, danışmanlık vb.
            $table->string('country', 100)->nullable();       // Türkiye, Fransa vb.
            $table->string('city', 100)->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();   // harita için enlem
            $table->decimal('longitude', 11, 8)->nullable();  // harita için boylam
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
