<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPricePerOneToCommercialOfferMaterialSplits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commercial_offer_material_splits', function (Blueprint $table) {
            $table->string('price_per_one')->nullable();
            $table->string('result_price')->nullable();
            $table->unsignedInteger('com_offer_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commercial_offer_material_splits', function (Blueprint $table) {
            $table->dropColumn('price_per_one');
            $table->dropColumn('result_price');
            $table->dropColumn('com_offer_id');
        });
    }
}