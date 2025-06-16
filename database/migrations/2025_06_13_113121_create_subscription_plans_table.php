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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'Free Trial', 'Annual Plan'
            $table->string('slug')->unique(); // e.g., 'free-trial', 'annual-plan'
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2)->default(0); // Price in your currency
            $table->string('currency', 3)->default('USD');
            $table->enum('interval', ['day', 'week', 'month', 'year'])->default('year');
            $table->integer('interval_count')->default(1); // e.g., 1 year, 6 months
            $table->integer('trial_period_days')->default(0);
            
            // Download limits
            $table->integer('daily_download_limit')->nullable(); // null = unlimited
            $table->integer('total_download_limit')->nullable(); // for trial plans
            
            // Stripe integration
            $table->string('stripe_price_id')->nullable();
            $table->string('stripe_product_id')->nullable();
            
            // Plan status and features
            $table->boolean('is_active')->default(true);
            $table->boolean('is_trial')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->json('features')->nullable(); // JSON array of features
            
            // Sorting and display
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
