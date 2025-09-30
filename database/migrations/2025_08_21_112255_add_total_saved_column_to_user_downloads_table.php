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
            $table->integer('saved_template_count')->default(0)->after('monthly_downloads_used')->comment("Total count of saved template");
            $table->integer('monthly_saved_template_count')->default(0)->after('saved_template_count')->comment("Total monthly count of saved template");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_downloads', function (Blueprint $table) {
            $table->dropColumn('saved_template_count');
            $table->dropColumn('monthly_saved_template_count');
        });
    }
};
