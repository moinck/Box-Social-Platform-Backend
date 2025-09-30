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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users','id')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans','id')->onDelete('cascade');
            
            // Stripe fields
            $table->string('stripe_subscription_id')->unique()->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->string('stripe_status')->nullable();
            $table->string('stripe_payment_method_id')->nullable();
            $table->json('response_meta')->nullable();
        
            // Payment tracking
            $table->decimal('amount_paid', 8, 2)->default(0);
            
            // Subscription status
            $table->enum('status', ['active', 'cancelled', 'past_due', 'unpaid', 'incomplete', 'trialing','canceled_extended'])
                    ->default('active');
            
            // Subscription periods
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_start')->nullable();
            $table->timestamp('trial_end')->nullable();
            
            // Cancellation
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('ends_at')->nullable();
        
            // Admin extension (for cancellation with extension)
            $table->timestamp('admin_extended_until')->nullable();
            $table->text('admin_notes')->nullable();
        
            // Cancellation bonus tracking
            $table->boolean('cancellation_bonus_used')->default(false);
            $table->timestamp('cancellation_bonus_granted_at')->nullable();
            $table->integer('cancellation_bonus_days')->default(0);
        
            // user download lmits
            $table->integer('total_download_limit')->default(0);
            $table->integer('daily_download_limit')->default(0);
            $table->integer('downloads_used_today')->default(0);
            $table->integer('total_downloads_used')->default(0);
            $table->date('daily_reset_date')->default(now()->format('Y-m-d'));
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
