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
        Schema::create('package_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();              // free / silver / gold
            $table->string('display_name', 100);               // Ücretsiz / Silver / Gold
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);
            $table->string('currency', 3)->default('TRY');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);         // ödeme sayfasındaki sıralama
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_definitions');
    }
};
