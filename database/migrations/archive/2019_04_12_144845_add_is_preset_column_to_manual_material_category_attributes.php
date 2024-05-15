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
        Schema::table('manual_material_category_attributes', function (Blueprint $table) {
            $table->boolean('is_preset')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('manual_material_category_attributes', function (Blueprint $table) {
            $table->dropColumn('is_preset');
        });
    }
};
