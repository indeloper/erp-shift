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
        Schema::table('technic_movements', function (Blueprint $table) {
            $table->dropForeign(['responsible_id']);
        });

        Schema::table('technic_movements', function (Blueprint $table) {
            $table->unsignedInteger('responsible_id')->nullable()->change();
            $table->foreign('responsible_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technic_movements', function (Blueprint $table) {
            //
        });
    }
};
