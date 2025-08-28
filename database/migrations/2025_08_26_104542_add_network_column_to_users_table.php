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
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('authorisation_type')->default(0)->comment("1 => Directly Authorised, 2 => Appointed Representative")->after('role');
            $table->string('appointed_network')->nullable()->after('authorisation_type');
            $table->tinyInteger('company_type')->default(0)->comment("1 => Solo Trader, 2 => Limited Company")->after('appointed_network');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            Schema::dropIfExists('authorisation_type');
            Schema::dropIfExists('appointed_network');
            Schema::dropIfExists('company_type');
        });
    }
};
