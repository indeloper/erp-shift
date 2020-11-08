<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Manual\ManualMaterialCategory;

use Illuminate\Support\Facades\DB;

class ChangeCategoryUnitInAllManualCategoriesWhereItNeed extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $categories = ManualMaterialCategory::whereIn('id', [10])->get();
        foreach ($categories as $category) {
            $attr_new = $category->attributes()->create([
                'name' => 'Удельный тоннаж',
                'description' => 'Кол-во тонн в штуке',
                'is_required' => 1,
                'unit' => 'т',
                'is_preset' => 1,
            ]);

            $attr_1 = $category->attributes()->whereName('Удельный погонаж')->first();
            $attr_2 = $category->attributes()->whereUnit('кг')->first();
            $attr_3 = $category->attributes()->create([
                'name' => 'Длина',
                'description' => 'Длина 1 штуки',
                'is_required' => 1,
                'unit' => 'м',
                'is_preset' => 0,
            ]);

            foreach ($category->materials as $material) {
                $material->parameters()->where('attr_id', $attr_1->id)->delete();
            }

            $category->category_unit = 'шт';
            $category->save();
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
