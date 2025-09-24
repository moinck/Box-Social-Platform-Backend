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
        Schema::table('post_templates3', function (Blueprint $table) {
            $table->string('template_url')->nullable()->after('template_data');
            $table->tinyInteger('is_uploaded')->nullable()->after('template_url')->comment('1 => Upoaded else not uploaded');
            $table->timestamp('uploaded_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_templates3', function (Blueprint $table) {
            $table->dropIfExists('template_url');
            $table->dropIfExists('is_uploaded');
            $table->dropIfExists('uploaded_at');
        });
    }
};
