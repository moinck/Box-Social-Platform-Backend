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
        Schema::create('user_downloads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('user_subscription_id');
                        
            // Download counters
            $table->integer('total_downloads_used')->default(0)->comment('Total downloads used in current period');
            $table->integer('monthly_downloads_used')->default(0)->comment('Downloads used in current month (for premium)');
            
            // Period tracking for monthly reset
            $table->integer('current_month')->default(1)->comment('Current tracking month (1-12)');
            $table->integer('current_year')->default(2025)->comment('Current tracking year');
            $table->date('last_reset_date')->nullable()->comment('Last monthly reset date for premium plans');
            
            // Plan limits (stored for quick access)
            $table->string('plan_type')->comment('basic, premium');
            $table->integer('monthly_limit')->default(0)->comment('Monthly download limit');
            $table->integer('total_limit')->default(0)->comment('Total limit for basic plans (3) and 40 for premium plans');
            
            // Carry over for premium plans
            $table->integer('carried_over_downloads')->default(0)->comment('Unused downloads from previous month');
            
            // Expiry for basic plans
            $table->timestamp('expires_at')->nullable()->comment('When basic plan downloads expire (3 days)');
            $table->timestamps();

            // Indexes and constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_subscription_id')->references('id')->on('user_subscriptions')->onDelete('cascade');
            $table->index(['user_id', 'current_month', 'current_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_downloads');
    }
};
