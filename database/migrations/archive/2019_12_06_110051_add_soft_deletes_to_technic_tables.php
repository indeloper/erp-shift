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
        Schema::table('our_technics', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('category_characteristics', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('technic_categories', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technic_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('our_technics', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('category_characteristics', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
