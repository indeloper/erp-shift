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
    public function up()
    {
        Schema::table('commercial_offer_material_splits', function (Blueprint $table) {
            \App\Models\CommercialOffer\CommercialOfferMaterialSplit::query()->update(['subcontractor_id' => null]);

            $table->renameColumn('subcontractor_id', 'subcontractor_file_id');
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
            $table->renameColumn('subcontractor_file_id', 'subcontractor_id');
        });
    }
};
