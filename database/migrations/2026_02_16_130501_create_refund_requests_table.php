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
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 50);                      // dissatisfaction / wrong_payment / technical_issue / other
            $table->text('description')->nullable();
            $table->decimal('refund_amount', 10, 2);           // kısmi iade için farklı tutar
            $table->string('status', 30)->default('pending');   // pending / approved / rejected / processing / completed
            $table->text('admin_note')->nullable();             // admin red/onay açıklaması
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('refund_ref', 255)->nullable();     // ödeme sağlayıcı iade referans no
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('decided_at')->nullable();        // onay veya red tarihi
            $table->timestamp('refunded_at')->nullable();       // paranın iade edildiği tarih
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
