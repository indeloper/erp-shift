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
    public function up(): void
    {
        DB::beginTransaction();

        $category = ManualMaterialCategory::find(9);
        $category->load('materials.parameters', 'attributes');
        $params = collect();

        foreach ($category->materials as $material) {
            $wide = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%'.'Толщина стенки'.'%');
            })->first();

            $lengthA = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%'.'Длина стороны а'.'%');
            })->first();

            $lengthB = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%'.'Длина стороны б'.'%');
            })->first();

            if (isset($wide->value) && isset($lengthA->value) && isset($lengthB->value)) {
                $params->push([
                    ['attr_id' => $wide->attr_id, 'value' => $wide->value],
                    ['attr_id' => $lengthA->attr_id, 'value' => $lengthA->value],
                    ['attr_id' => $lengthB->attr_id, 'value' => $lengthB->value],
                ]);
            }
        }

        $category->attributes()->whereNotIn('name',
            [
                'Толщина стенки',
                'Длина стороны а',
                'Длина стороны б',
                'Масса 1 м.п.',
            ]
        )->update(['is_display' => 0]);

        // create ManualReference with parameters based on exist materials
        foreach ($params->unique() as $index => $item) {
            dump($index.' in '.$params->unique()->count());

            $newReference = ManualReference::create([
                'name' => 'Труба профильная '.$item[0]['value'].'*'.$item[1]['value'].'*'.$item[2]['value'],
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
                        'Толщина стенки',
                        'Длина стороны а',
                        'Длина стороны б',
                        'Масса 1 м.п.',
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
    public function down(): void
    {
        $manualReferences = ManualReference::where('category_id', 9)->get();

        foreach ($manualReferences as $index => $manualReference) {
            $manualReference->parameters()->delete();
            $manualReference->delete();
        }
    }
};
