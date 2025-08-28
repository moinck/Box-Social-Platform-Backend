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
            $table->string('coupon_id')->nullable()->after('invoice_number')->comment('Coupon Promotion Code Id');
            $table->string('coupon_code')->nullable()->after('coupon_id')->comment('Coupon Code');
            $table->string('coupon_name')->nullable()->after('coupon_code')->comment('Coupon Name');
            $table->string('coupon_type')->nullable()->after('coupon_name')->comment('Coupon type: amount, percent');
            $table->decimal('coupon_discount',10,2)->nullable()->after('coupon_type')->comment('Coupon discount number');
            $table->decimal('coupon_discounted_amt',10,2)->nullable()->after('coupon_discount')->comment('Coupon discounted amount');
            $table->string('coupon_currency')->nullable()->after('coupon_discount')->comment('Coupon currency');
            $table->string('coupon_discount_id')->nullable()->after('coupon_currency')->comment('Coupon discount id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            Schema::dropIfExists('coupon_id');
            Schema::dropIfExists('coupon_code');
            Schema::dropIfExists('coupon_name');
            Schema::dropIfExists('coupon_type');
            Schema::dropIfExists('coupon_discount');
            Schema::dropIfExists('coupon_discounted_amt');
            Schema::dropIfExists('coupon_currency');
            Schema::dropIfExists('coupon_discount_id');
        });
    }
};
