<?php

use App\Models\CommercialOffer\CommercialOfferMaterialSplit;
use App\Models\WorkVolume\WorkVolumeMaterial;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeWvMatIdColumnInCommercialOfferMaterialSplitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $old_splits = CommercialOfferMaterialSplit::get();
        foreach ($old_splits as $split) {
            $wv_mat = WorkVolumeMaterial::find($split->wv_mat_id);
            if ($wv_mat) {
                $split->wv_mat_id = $wv_mat->manual_material_id;
                $split->save();
            } else {
                $split->delete();
            }
        }
        Schema::table('commercial_offer_material_splits', function (Blueprint $table) {
            $table->renameColumn('wv_mat_id', 'man_mat_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $old_splits = CommercialOfferMaterialSplit::get();
        foreach ($old_splits as $split) {
            $wv_mat = WorkVolumeMaterial::where('work_volume_id', $split->work_volume_id)->where('manual_material_id', $split->man_mat_id)->first();
            $split->man_mat_id = $wv_mat->id;
            $split->save();
        }
        Schema::table('commercial_offer_material_splits', function (Blueprint $table) {
            $table->renameColumn('man_mat_id', 'wv_mat_id');
        });
    }
}
