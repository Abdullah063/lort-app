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
        Schema::create('membership_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->constrained('memberships');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('previous_package_id')->nullable()->constrained('package_definitions');
            $table->foreignId('new_package_id')->nullable()->constrained('package_definitions');
            $table->string('change_reason', 50);               // purchase / upgrade / downgrade / refund / expired / admin_action
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_history');
    }
};
