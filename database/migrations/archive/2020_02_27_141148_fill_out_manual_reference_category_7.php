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
     */
    public function up(): void
    {
        DB::beginTransaction();

        $category = ManualMaterialCategory::find(7);
        $category->load('materials.parameters', 'attributes');
        $params = collect();

        foreach ($category->materials as $material) {
            $diameter = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%'.'диаметр'.'%');
            })->first();

            $wide = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%'.'толщина'.'%');
            })->first();

            if (isset($diameter->value) && isset($wide->value)) {
                $params->push([
                    ['attr_id' => $diameter->attr_id, 'value' => $diameter->value],
                    ['attr_id' => $wide->attr_id, 'value' => $wide->value],
                ]);
            }
        }

        $category->attributes()->whereNotIn('name',
            [
                'Диаметр',
                'Толщина стенки трубы',
                'Масса 1 м.п.',
            ])->update(['is_display' => 0]);

        // create ManualReference with parameters based on exist materials
        foreach ($params->unique() as $index => $item) {
            dump($index.' in '.$params->unique()->count());

            $newReference = ManualReference::create([
                'name' => 'Труба '.$item[0]['value'].'*'.$item[1]['value'],
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
                        'Толщина стенки трубы',
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
     */
    public function down(): void
    {
        $manualReferences = ManualReference::where('category_id', 7)->get();

        foreach ($manualReferences as $index => $manualReference) {
            $manualReference->parameters()->delete();
            $manualReference->delete();
        }
    }
};
