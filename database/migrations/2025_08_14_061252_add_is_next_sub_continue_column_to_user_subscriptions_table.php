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
            $table->boolean('is_next_sub_continue')->nullable()->comment("Purchased Canceled subscription continue or cancel?")->after('reset_date');
            $table->boolean('is_subscription_cancel')->nullable()->comment("Subscription canceled or not")->after('is_next_sub_continue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn('is_next_sub_continue');
            $table->dropColumn('is_subscription_cancel');
        });
    }
};
