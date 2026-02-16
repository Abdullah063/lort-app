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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_code', 50);               // welcome / package_congrats / receipt
            $table->string('language_code', 5);
            $table->string('title', 200);
            $table->text('content');                            // Merhaba {{name}}, hoş geldiniz!
            $table->timestamps();

            $table->unique(['template_code', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
