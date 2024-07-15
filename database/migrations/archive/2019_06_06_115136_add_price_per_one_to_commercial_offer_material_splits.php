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
        Schema::table('commercial_offer_material_splits', function (Blueprint $table) {
            $table->string('price_per_one')->nullable();
            $table->string('result_price')->nullable();
            $table->unsignedInteger('com_offer_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commercial_offer_material_splits', function (Blueprint $table) {
            $table->dropColumn('price_per_one');
            $table->dropColumn('result_price');
            $table->dropColumn('com_offer_id');
        });
    }
};
