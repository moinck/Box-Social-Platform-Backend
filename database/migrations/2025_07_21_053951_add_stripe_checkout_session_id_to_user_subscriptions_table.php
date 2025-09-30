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
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->after('stripe_subscription_id');
            $table->string('currency', 3)->default('GBP')->after('amount_paid');
            $table->timestamp('last_payment_date')->nullable()->after('current_period_end');
            $table->integer('failed_payment_attempts')->default(0)->after('last_payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn('stripe_checkout_session_id');
            $table->dropColumn('currency');
            $table->dropColumn('last_payment_date');
            $table->dropColumn('failed_payment_attempts');
        });
    }
};
