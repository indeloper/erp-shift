<?php

use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AngleAddNewAttrManual extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $category_angle = ManualMaterialCategory::find(11);
        $category_angle->attributes()->create([
            'name' => 'Ширина полки',
            'description' => 'Ширина полки',
            'is_required' => 1,
            'unit' => 'мм',
            'is_preset' => 0,
            'step' => 5,
            'from' => 20,
            'to' => 250,
        ]);
        $category_angle->attributes()->create([
            'name' => 'Толщина',
            'description' => 'Толщина',
            'is_required' => 1,
            'unit' => 'мм',
            'is_preset' => 0,
            'step' => 1,
            'from' => 3,
            'to' => 20,
        ]);

        $category_angle->attributes()->create([
            'name' => 'Материал',
            'description' => '',
            'is_required' => 0,
            'unit' => '',
            'is_preset' => 0,
        ]);

        $attr_width = $category_angle->attributes()->where('name', 'like', '%'.'Ширина полки'.'%')->first();
        $attr_thickness = $category_angle->attributes()->where('name', 'like', '%'.'Толщина'.'%')->first();
        $attr_material = $category_angle->attributes()->where('name', 'like', '%'.'Материал'.'%')->first();

        foreach ($category_angle->materials as $material) {
            $explode = explode(' ', $material->name);

            $params = explode('*', $explode[2]);

            if (! isset($params[1])) {
                $params = explode('х', $explode[2]);
            }

            $material->parameters()->create([
                'attr_id' => $attr_width->id,
                'value' => $params[0],
            ]);
            $material->parameters()->create([
                'attr_id' => $attr_thickness->id,
                'value' => $params[1],
            ]);

            if (isset($explode[4])) {
                $material->parameters()->create([
                    'attr_id' => $attr_material->id,
                    'value' => $explode[4],
                ]);
            }
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
        DB::beginTransaction();

        $category_angle = ManualMaterialCategory::find(11);

        $attr_width = $category_angle->attributes()->where('name', 'like', '%'.'Ширина полки'.'%')->delete();
        $attr_thickness = $category_angle->attributes()->where('name', 'like', '%'.'Толщина'.'%')->delete();
        $attr_material = $category_angle->attributes()->where('name', 'like', '%'.'Материал'.'%')->delete();

        DB::commit();
    }
}
