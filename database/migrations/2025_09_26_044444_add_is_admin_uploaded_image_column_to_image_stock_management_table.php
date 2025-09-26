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
        Schema::table('image_stock_management', function (Blueprint $table) {
            $table->tinyInteger('is_admin_uploaded')->default(0)->after('image_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_stock_management', function (Blueprint $table) {
            $table->dropColumn('is_admin_uploaded');
        });
    }
};
