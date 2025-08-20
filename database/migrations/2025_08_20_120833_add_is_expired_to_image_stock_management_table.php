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
            $table->boolean('is_expired')->default(0);
            $table->string('old_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_stock_management', function (Blueprint $table) {
            $table->boolean('is_expired')->default(0);
            $table->string('old_url')->nullable();
        });
    }
};
