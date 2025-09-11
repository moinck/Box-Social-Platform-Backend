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
        Schema::table('fca_numbers', function (Blueprint $table) {
            $table->timestamp('account_deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fca_numbers', function (Blueprint $table) {
            $table->dropIfExists('account_deleted_at');
        });
    }
};
