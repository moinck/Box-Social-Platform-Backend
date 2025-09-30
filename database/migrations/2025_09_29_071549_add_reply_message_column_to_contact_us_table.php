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
        Schema::table('contact_us', function (Blueprint $table) {
            $table->string('email_subject')->after('message')->nullable();
            $table->text('feedback_reply')->after('email_subject')->nullable();
            $table->boolean('is_replied')->after('feedback_reply')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_us', function (Blueprint $table) {
            $table->dropIfExists('email_subject');
            $table->dropIfExists('feedback_reply');
            $table->dropIfExists('is_replied');
        });
    }
};
