<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubcontractorIdColumnToCommercialOfferMaterialSplitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commercial_offer_material_splits', function (Blueprint $table) {
            $table->unsignedInteger('subcontractor_id')->nullable();
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
            $table->dropColumn('subcontractor_id');
        });
    }
}
