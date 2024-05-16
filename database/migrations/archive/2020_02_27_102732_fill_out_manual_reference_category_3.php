<?php

use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualReference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FillOutManualReferenceCategory3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $category = ManualMaterialCategory::find(3);
        $category->load('materials.parameters', 'attributes');
        $params = collect();

        foreach ($category->materials as $material) {
            $mark = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%'.'Марка'.'%');
            })->first();

            $diameter = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%'.'Диаметр'.'%');
            })->first();

            if (isset($mark->value) && isset($diameter->value)) {
                $params->push([
                    ['attr_id' => $mark->attr_id, 'value' => $mark->value],
                    ['attr_id' => $diameter->attr_id, 'value' => $diameter->value],
                ]);
            }
        }

        $category->attributes()->whereNotIn('name',
            [
                'Диаметр',
                'Длина',
                'Поверхность',
                'Вид проката',
                'Временное сопротивление разрыву',
                'Предел текучести',
                'Относительное удлинение',
                'Марка стали',
            ]
        )->update(['is_display' => 0]);

        // create ManualReference with parameters based on exist materials
        foreach ($params->unique() as $index => $item) {
            dump($index.' in '.$params->unique()->count());

            $newReference = ManualReference::create([
                'name' => 'Арматура периодич '.$item[0]['value'].' d'.$item[1]['value'].'мм',
                'category_id' => $category->id,
            ]);

            $material = ManualMaterial::with('parameters')
                ->where('category_id', $category->id)
                ->whereHas('parameters', function ($q) use ($item) {
                    $q->whereIn('value', [$item[0]['value'], $item[1]['value']]);
                })
                ->first();

            $parameters = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('is_preset', 0);
            })->get();

            foreach ($parameters as $parameter) {
                if (in_array($parameter->name,
                    [
                        'Диаметр',
                        'Длина',
                        'Поверхность',
                        'Вид проката',
                        'Временное сопротивление разрыву',
                        'Предел текучести',
                        'Относительное удлинение',
                        'Марка стали',
                    ])) {
                    $newReference->parameters()->create([
                        'attr_id' => $parameter->attr_id,
                        'value' => $parameter->value,
                    ]);
                }
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
        $manualReferences = ManualReference::where('category_id', 3)->get();

        foreach ($manualReferences as $index => $manualReference) {
            $manualReference->parameters()->delete();
            $manualReference->delete();
        }
    }
}
