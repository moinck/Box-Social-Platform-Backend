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
            $table->unsignedBigInteger('post_content_id')->nullable()->after('design_style_id');
            $table->foreign('post_content_id')->references('id')->on('post_contents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_templates', function (Blueprint $table) {
            $table->dropForeign(['post_content_id']);
            $table->dropIndex(['post_content_id']);
            $table->dropColumn('post_content_id');
        });
    }
};
