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
            $table->json('category_id_json')->after('category_id')->nullable();
            $table->json('sub_category_id_json')->after('sub_category_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_templates', function (Blueprint $table) {
            $table->dropColumn('category_id_json');
            $table->dropColumn('sub_category_id_json');
        });
    }
};
