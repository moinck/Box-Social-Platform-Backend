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
        Schema::table('user_downloads', function (Blueprint $table) {
            $table->integer('monthly_saved_limit')->default(0)->after('total_limit')->comment('Monthly template saved limit');
            $table->integer('total_saved_limit')->default(0)->after('monthly_saved_limit')->comment('Total limit for saved template, basic plans (3) and 40 for premium plans	');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_downloads', function (Blueprint $table) {
            $table->dropIfExists('monthly_saved_limit');
            $table->dropIfExists('total_saved_limit');
        });
    }
};
