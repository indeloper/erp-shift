<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Manual\ManualMaterialCategory;

use Illuminate\Support\Facades\DB;

class AddMarksAndAnalogesToMaterials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        // NEW ATTRS FOR TONGUE
        $category_tongue = ManualMaterialCategory::find(2);
        $attr_clin = $category_tongue->attributes()->where('name', 'like', '%' . 'клиновидный' . '%')->first();

        foreach ($category_tongue->materials()->where('name', 'like', '%' . 'клиновидный' . '%')->get() as $material) {
            $material->parameters()->create([
                'attr_id' => $attr_clin->id,
                'value' => 'Да'
            ]);
        }

        $attr_pile_tongue = $category_tongue->attributes()->where('name', 'like', '%' . 'Трубошпунт' . '%')->first();

        foreach ($category_tongue->materials()->where('name', 'like', '%' . 'Трубошпунт' . '%')->get() as $material) {
            $material->parameters()->create([
                'attr_id' => $attr_pile_tongue->id,
                'value' => 'Да'
            ]);
        }

        $marks = [];
        $attr_mark = $category_tongue->attributes()->where('name', 'like', '%' . 'марка' . '%')->first();

        foreach ($category_tongue->materials as $material) {
            $explode = explode(' ', $material->name);
            if ($explode[0] != 'Трубошпунт') {
                if ($explode[0] == 'Клиновидный') {
                    $mark = $explode[2];
                } else if (isset($explode[4]) && $explode[4] == 'метров') {
                    $mark = ($explode[1] . ' ' . $explode[2]);
                } else {
                    $mark = $explode[1];
                }
            }
            $marks[] = $mark;
            if ($explode[0] != 'Трубошпунт') {
                $material->parameters()->create([
                    'attr_id' => $attr_mark->id,
                    'value' => $mark
                ]);
            }
        }

        $attr_analogs = $category_tongue->attributes()->where('name', 'like', '%' . 'Аналоги' . '%')->first();

        foreach ($category_tongue->materials as $material) {
            $explode = explode(' ', $material->name);
            if ($explode[0] != 'Трубошпунт') {
                if ($explode[count($explode) - 1][0] == '(') {
                    $material->parameters()->create([
                        'attr_id' => $attr_analogs->id,
                        'value' => $explode[count($explode) - 1]
                    ]);
                }
            }
        }

        // new attrs for beam
        $category_beam = ManualMaterialCategory::find(4);
        $attr_mark = $category_beam->attributes()->where('name', 'like', '%' . 'марка' . '%')->first();

        foreach ($category_beam->materials as $material) {
            $explode = explode(' ', $material->name);

            $material->parameters()->create([
                'attr_id' => $attr_mark->id,
                'value' => $explode[1]
            ]);
        }

        // new attribute for sheet
        $category_sheet = ManualMaterialCategory::find(5);
        $attr_thickness = $category_sheet->attributes()->where('name', 'like', '%' . 'Толщина' . '%')->first();
        $attr_length = $category_sheet->attributes()->where('name', 'like', '%' . 'Длина' . '%')->first();
        $attr_width = $category_sheet->attributes()->where('name', 'like', '%' . 'Ширина' . '%')->first();

        foreach ($category_sheet->materials()->whereIn('id', [359143, 359144, 359145])->get() as $material) {
            if ($material->id == 359143) {
                $material->name = 'Лист г/к - 16х360х190';
            } else if ($material->id == 359144) {
                $material->name = 'Лист г/к - 16х500х500';
            } else if ($material->id == 359145) {
                $material->name = 'Лист г/к - 16х700х400';
            }
            $material->save();
        }

        foreach ($category_sheet->materials as $material) {

            $explode = explode(' ', $material->name);
            // dump($explode);

            if (isset($explode[2]) && strlen($explode[2]) <= 2 && !isset($explode[3])) {
                $material->parameters()->create([
                    'attr_id' => $attr_thickness->id,
                    'value' => $explode[2]
                ]);
            } else if (isset($explode[3])) {
                $explode_params = explode('х', $explode[3]);

                $material->parameters()->create([
                    'attr_id' => $attr_thickness->id,
                    'value' => $explode_params[0]
                ]);

                $material->parameters()->create([
                    'attr_id' => $attr_length->id,
                    'value' => $explode_params[1]
                ]);

                $material->parameters()->create([
                    'attr_id' => $attr_width->id,
                    'value' => $explode_params[2]
                ]);
            }
        }

        $category_channel = ManualMaterialCategory::find(6);
        $attr_series = $category_channel->attributes()->where('name', 'like', '%' . 'Серия' . '%')->first();

        foreach ($category_channel->materials as $material) {
            $material->parameters()->create([
                'attr_id' => $attr_series->id,
                'value' => mb_substr($material->name, -1)
            ]);
        }

        $category_angle = ManualMaterialCategory::find(10);
        $attr_mark = $category_angle->attributes()->where('name', 'like', '%' . 'марка' . '%')->first();
        $attr_analogs = $category_angle->attributes()->where('name', 'like', '%' . 'Аналоги' . '%')->first();

        foreach ($category_angle->materials as $material) {
            $explode = explode(' ', $material->name);

            if (isset($explode[3]) && $explode[3] == 13) {
                $material->parameters()->create([
                    'attr_id' => $attr_mark->id,
                    'value' => ($explode[2] . ' ' . $explode[3])
                ]);
            } else {
                $material->parameters()->create([
                    'attr_id' => $attr_mark->id,
                    'value' => $explode[2]
                ]);
            }

            if (isset($explode[3]) && $explode[3] != 13) {
                $material->parameters()->create([
                    'attr_id' => $attr_analogs->id,
                    'value' => $explode[3]
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

    }
}
