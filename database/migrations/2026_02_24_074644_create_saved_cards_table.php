<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('card_user_key');          // iyzico cardUserKey
            $table->string('card_token');             // iyzico cardToken
            $table->string('card_alias')->nullable(); // kartın takma adı
            $table->string('last_four', 4);           // son 4 hane (gösterim için)
            $table->string('card_brand')->nullable(); // VISA / MASTERCARD vb.
            $table->string('card_type')->nullable();  // CREDIT_CARD / DEBIT_CARD
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_cards');
    }
};