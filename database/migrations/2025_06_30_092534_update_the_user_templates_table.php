<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('user_templates', function (Blueprint $table) {
            // 1. First make template_id nullable temporarily
            $table->unsignedBigInteger('template_id')->nullable()->change();
            
            // 2. Add new category_id column
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->onDelete('cascade')
                  ->after('template_id');
        });

        // 3. Backfill category_id for existing records
        DB::statement('
            UPDATE user_templates ut
            JOIN post_templates pt ON ut.template_id = pt.id
            SET ut.category_id = pt.category_id
            WHERE ut.template_id IS NOT NULL
        ');

        // 4. Now modify the foreign key constraint
        Schema::table('user_templates', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->foreign('template_id')
                  ->references('id')
                  ->on('post_templates')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_templates', function (Blueprint $table) {
            // Reverse the changes
            $table->dropForeign(['template_id']);
            $table->dropForeign(['category_id']);
            
            // Before making template_id non-nullable, ensure no nulls exist
            DB::statement('UPDATE user_templates SET template_id = 0 WHERE template_id IS NULL');
            
            $table->unsignedBigInteger('template_id')->nullable(false)->change();
            $table->foreign('template_id')
                  ->references('id')
                  ->on('post_templates')
                  ->onDelete('cascade');
            
            $table->dropColumn('category_id');
        });
    }
};
