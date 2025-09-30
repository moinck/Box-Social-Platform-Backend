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
            // Basic payment info
            $table->foreignId('user_id')->constrained('users','id')->onDelete('cascade');
            $table->string('plan_name')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'partially_refunded'])->default('pending');
            
            // Amount details
            $table->decimal('amount', 10, 2)->comment('Amount in base currency unit');
            $table->string('currency', 3)->default('GBP');
            $table->string('payment_type')->comment('subscription, one_time, refund, etc.');
            $table->string('payment_method')->default('card')->comment('card, bank_transfer, wallet, etc.');

            // Stripe related fields
            $table->string('stripe_payment_intent_id')->nullable();
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
