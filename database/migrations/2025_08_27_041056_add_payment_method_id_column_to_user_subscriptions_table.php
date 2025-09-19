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
            $table->string('client_secret')->nullable()->after('stripe_payment_method_id');
            $table->string('stripe_payment_intent_id')->nullable()->after('client_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            Schema::dropIfExists('client_secret');
            Schema::dropIfExists('stripe_payment_intent_id');
        });
    }
};
