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
        Schema::table('brand_kits', function (Blueprint $table) {
            $table->string('warning_title')->nullable()->after('design_style');
            $table->string('warning_message')->nullable()->after('warning_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brand_kits', function (Blueprint $table) {
            $table->dropColumn('warning_title');
            $table->dropColumn('warning_message');
        });
    }
};
