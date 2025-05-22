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
        Schema::create('brand_kits', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger("user_id")->nullable();
            $table->text("logo")->nullable(); 
            $table->longText("color")->nullable(); 
            $table->text("company_name")->nullable();
            $table->text("font")->nullable(); 
            $table->text("email")->nullable(); 
            $table->text("address")->nullable(); 
            $table->text("state")->nullable(); 
            $table->text("phone")->nullable(); 
            $table->text("country")->nullable(); 
            $table->text("website")->nullable(); 
            $table->text("postal_code")->nullable();

            $table->text("show_email_on_post")->nullable();
            $table->text("show_phone_number_on_post")->nullable(); 
            $table->text("show_website_on_post")->nullable(); 

            $table->text("design_style")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_kits');
    }
};
