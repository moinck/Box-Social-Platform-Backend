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
            $table->tinyInteger('is_mail_send')->default(1)->after('coupon_discounted_amt');
            $table->timestamp('webhook_called_at')->nullable()->after('is_mail_send');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropIfExists('is_mail_send');
            $table->dropIfExists('webhook_called_at');
        });
    }
};
