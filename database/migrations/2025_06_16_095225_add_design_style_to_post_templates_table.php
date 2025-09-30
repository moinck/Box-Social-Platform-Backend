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
        Schema::table('post_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('design_style_id')->nullable()->after('category_id');
            $table->foreign('design_style_id')->references('id')->on('design_styles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_templates', function (Blueprint $table) {
            $table->dropForeign(['design_style_id']);
            $table->dropColumn('design_style_id');
        });
    }
};
