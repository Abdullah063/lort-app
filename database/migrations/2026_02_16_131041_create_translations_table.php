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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 100);                 // goals, interests, package_definitions
            $table->unsignedBigInteger('record_id');            // ilgili tablodaki satırın id'si
            $table->string('field_name', 100);                  // name / description / feature_name
            $table->string('language_code', 5);
            $table->text('value');                              // çevrilmiş metin
            $table->timestamps();

            $table->unique(['table_name', 'record_id', 'field_name', 'language_code'], 'translations_unique');
            $table->index('language_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
