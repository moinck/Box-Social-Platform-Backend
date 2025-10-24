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
        Schema::create('post_content_months', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('post_content_id');
            $table->unsignedBigInteger('month_id');
            $table->timestamps();

            $table->foreign('post_content_id')->references('id')->on('post_contents')->onDelete('cascade');
            $table->foreign('month_id')->references('id')->on('months')->onDelete('cascade');
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_content_months');
    }
};
