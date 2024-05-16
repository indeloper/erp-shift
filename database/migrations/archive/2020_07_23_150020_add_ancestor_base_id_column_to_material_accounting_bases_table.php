<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAncestorBaseIdColumnToMaterialAccountingBasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('material_accounting_bases', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor_base_id')->nullable();
        });

        DB::beginTransaction();

        $today_bases = \App\Models\MatAcc\MaterialAccountingBase::where('transferred_today', 0)->get();
        foreach ($today_bases as $today_base) {
            $ancestor_id = \App\Models\MatAcc\MaterialAccountingBase::where([
                'object_id' => $today_base->object_id,
                'manual_material_id' => $today_base->manual_material_id,
                'used' => $today_base->used,
            ])->select('id')->first()->id;

            \App\Models\MatAcc\MaterialAccountingBase::where([
                'object_id' => $today_base->object_id,
                'manual_material_id' => $today_base->manual_material_id,
                'used' => $today_base->used,
            ])->update(['ancestor_base_id' => $ancestor_id]);
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('material_accounting_bases', function (Blueprint $table) {
            $table->dropColumn('ancestor_base_id');
        });
    }
}
