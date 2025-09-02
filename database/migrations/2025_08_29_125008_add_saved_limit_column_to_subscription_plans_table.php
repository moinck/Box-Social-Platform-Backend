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
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('monthly_download_limit')->nullable()->after('total_download_limit');
            $table->integer('total_saved_limit')->nullable()->after('monthly_download_limit');
            $table->integer('monthly_saved_limit')->nullable()->after('total_saved_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropIfExists('monthly_download_limit');
            $table->dropIfExists('total_saved_limit');
            $table->dropIfExists('monthly_saved_limit');
        });
    }
};
