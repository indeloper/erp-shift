<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Manual\ManualMaterialCategory;


class AddUnitWeightToSheetGkToManualMaterialParameters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        $category = ManualMaterialCategory::with('materials')->where('id', 5)->first();

        $attr_new = $category->attributes()->create([
            'name' => 'Удельный тоннаж',
            'description' => 'Кол-во тонн в штуке',
            'is_required' => 1,
            'unit' => 'т',
            'is_preset' => 1,
        ]);

        foreach ($category->materials as $material) {
            $weight_per_square_meter = (float)str_replace(',','.', $material->parameters()->where('name', 'Удельная площадь')->first()->value ?? 0);
            $square_meter = (float)str_replace(',','.', $material->parameters()->where('name', 'Масса 1 м2')->first()->value ?? 0);

            $material->parameters()->create([
                'attr_id' => $attr_new->id,
                'value' => (($weight_per_square_meter * $square_meter) / 1000)
            ]);
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