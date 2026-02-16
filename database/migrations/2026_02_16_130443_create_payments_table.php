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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('membership_id')->constrained('memberships');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('TRY');
            $table->string('status', 30)->default('pending');  // pending / completed / failed / refunded / partial_refund
            $table->string('payment_method', 30)->nullable();  // credit_card / bank_transfer / apple_pay / google_pay
            $table->string('provider_ref', 255)->nullable();   // ödeme sağlayıcı referans no
            $table->boolean('receipt_sent')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
