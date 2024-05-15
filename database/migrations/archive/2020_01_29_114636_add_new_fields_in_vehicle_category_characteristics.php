<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('vehicle_category_characteristics', function (Blueprint $table) {
            $table->boolean('required')->default(0)->after('show');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('vehicle_category_characteristics', function (Blueprint $table) {
            $table->dropColumn('required');
        });
    }
};
