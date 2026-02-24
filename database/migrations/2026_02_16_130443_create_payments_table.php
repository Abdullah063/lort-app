<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('membership_id')->constrained('memberships');
            $table->foreignId('saved_card_id')->nullable()->constrained('saved_cards')->nullOnDelete();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('TRY');
            $table->integer('installment')->default(1);

            $table->string('status', 30)->default('pending');  // pending / completed / failed / refunded / partial_refund
            $table->string('payment_method', 30)->nullable();  // credit_card / apple_pay / google_pay

            $table->string('provider_ref', 255)->nullable();   // ödeme sağlayıcı referans no (iyzico payment id vb.)
            $table->json('provider_meta')->nullable();          // ek bilgiler (conversation_id, token vb.)

            $table->string('error_code')->nullable();
            $table->string('error_message')->nullable();

            $table->boolean('receipt_sent')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
