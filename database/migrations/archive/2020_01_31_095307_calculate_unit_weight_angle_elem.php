<?php

use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Database\Migrations\Migration;

class CalculateUnitWeightAngleElem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $angleCategory = ManualMaterialCategory::find(10);
        $angleCategory->load('attributes', 'materials.parameters');

        $attr_1 = $angleCategory->attributes()->where('name', 'like', '%Удельный погонаж%')->first();
        $attr_2 = $angleCategory->attributes()->where('name', 'like', '%Длина%')->first();
        $attr_3 = $angleCategory->attributes()->where('name', 'like', '%Удельный тоннаж%')->first();
        $attr_4 = $angleCategory->attributes()->where('name', 'like', '%МАССА 1 М.П%')->first();

        foreach ($angleCategory->materials as $material) {
            $material->parameters()->updateOrCreate(['attr_id' => $attr_1->id], ['value' => 11.8]);
            $material->parameters()->updateOrCreate(['attr_id' => $attr_2->id], ['value' => 11.8]);
            $weigth_meter_unit = $material->parameters()->where('attr_id', $attr_4->id)->first()->value ?? 0;

            $material->parameters()->updateOrCreate(['attr_id' => $attr_3->id], ['value' => ((11.8 * $weigth_meter_unit) / 1000)]);
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

    }
}
