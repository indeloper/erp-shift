<?php

use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualRelationMaterialWork;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $manual_compact_pile = ManualMaterial::whereIn('category_id', [12, 14])->pluck('id')->toArray();

        $result = [];
        foreach ($manual_compact_pile as $material_id) {
            foreach ([67, 72, 73, 74, 77] as $work_id) {
                ManualRelationMaterialWork::insert(['manual_material_id' => $material_id, 'manual_work_id' => $work_id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
