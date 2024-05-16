<?php

use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualReference;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $category = ManualMaterialCategory::find(11);
        $category->load('materials.parameters', 'attributes');
        $params = collect();

        foreach ($category->materials as $material) {
            $param1 = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%'.'Ширина'.'%');
            })->first();

            $param2 = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%'.'Толщина'.'%');
            })->first();

            $param3 = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%'.'Материал'.'%');
            })->first();

            if (isset($param1->value) && isset($param2->value) && isset($param3->value)) {
                $params->push([
                    ['attr_id' => $param1->attr_id, 'value' => $param1->value],
                    ['attr_id' => $param2->attr_id, 'value' => $param2->value],
                    ['attr_id' => $param3->attr_id, 'value' => $param3->value],
                ]);
            }
        }

        $category->attributes()->whereNotIn('name',
            [
                'Ширина полки',
                'Толщина',
                'Материал',
            ]
        )->update(['is_display' => 0]);

        // create ManualReference with parameters based on exist materials
        foreach ($params->unique() as $index => $item) {
            dump($index.' in '.$params->unique()->count());

            $newReference = ManualReference::create([
                'name' => 'Уголок горячекатанный '.$item[0]['value'].'*'.$item[1]['value'].' '.$item[2]['value'],
                'category_id' => $category->id,
            ]);

            $material = ManualMaterial::with('parameters')
                ->where('category_id', $category->id)
                ->whereHas('parameters', function ($q) use ($item) {
                    $q->whereIn('value', [$item[0]['value'], $item[1]['value'], $item[2]['value']]);
                })
                ->first();

            $parameters = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('is_preset', 0);
            })->get();

            foreach ($parameters as $parameter) {
                if (in_array($parameter->name,
                    [
                        'Ширина полки',
                        'Толщина',
                        'Материал',
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
        $manualReferences = ManualReference::where('category_id', 11)->get();

        foreach ($manualReferences as $index => $manualReference) {
            $manualReference->parameters()->delete();
            $manualReference->delete();
        }
    }
};
