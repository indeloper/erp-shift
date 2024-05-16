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

        $category = ManualMaterialCategory::find(4);
        $category->load('materials.parameters', 'attributes');
        $params = collect();

        foreach ($category->materials as $material) {
            $mark = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%'.'Марка'.'%');
            })->first();

            if (isset($mark->value)) {
                $params->push(
                    ['attr_id' => $mark->attr_id, 'value' => $mark->value]
                );
            }
        }

        $category->attributes()->whereNotIn('name',
            [
                'Площадь поперечного сечения',
                'Высота двутавра h',
                'Масса 1 м.п.',
                'Момент инерции Ix',
                'Момент сопротивления Wx',
                'Радиус сопряжения R',
                'Толщина полки t',
                'Толщина стенки s',
                'Ширина полки b',
                'Марка',
            ])->update(['is_display' => 0]);

        // create ManualReference with parameters based on exist materials
        foreach ($params->unique() as $index => $item) {
            dump($index.' in '.$params->unique()->count());

            $newReference = ManualReference::create([
                'name' => 'Балка '.$item['value'],
                'category_id' => $category->id,
            ]);

            $material = ManualMaterial::with('parameters')
                ->where('category_id', $category->id)
                ->whereHas('parameters', function ($q) use ($item) {
                    $q->where('value', $item['value']);
                })
                ->first();

            $parameters = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('is_preset', 0);
            })->get();

            foreach ($parameters as $parameter) {
                if (in_array($parameter->name,
                    [
                        'Площадь поперечного сечения',
                        'Высота двутавра h',
                        'Масса 1 м.п.',
                        'Момент инерции Ix',
                        'Момент сопротивления Wx',
                        'Радиус сопряжения R',
                        'Толщина полки t',
                        'Толщина стенки s',
                        'Ширина полки b',
                        'Марка',
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
        $manualReferences = ManualReference::where('category_id', 4)->get();

        foreach ($manualReferences as $index => $manualReference) {
            $manualReference->parameters()->delete();
            $manualReference->delete();
        }
    }
};
