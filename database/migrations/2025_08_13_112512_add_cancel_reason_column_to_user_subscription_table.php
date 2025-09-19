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
            $table->string('sub_cancel_reason')->nullable()->after('reset_date');
            $table->string('child_sub_cancel_reason')->nullable()->after('sub_cancel_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn('sub_cancel_reason');
            $table->dropColumn('child_sub_cancel_reason');
        });
    }
};
