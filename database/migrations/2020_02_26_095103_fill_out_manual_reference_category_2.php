<?php

use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualReference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Manual\ManualMaterialCategory;


class FillOutManualReferenceCategory2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $category_2 = ManualMaterialCategory::find(2);
        $category_2->load('materials.parameters', 'attributes');
        $marks = collect();
        $markOther = collect();

        foreach ($category_2->materials as $material) {
            $type = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%' . 'Трубошпунт' . '%');
            })->first();

            $mark = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%' . 'Марка' . '%');
            })->first();

            if (!isset($type->value) || $type->value != 'Да') {
                $marks->push(['attr_id' => $mark->attr_id, 'value' => $mark->value]);
            } elseif (isset($type->value) && $type->value == 'Да') {
                $markOther->push(['attr_id' => $mark->attr_id, 'value' => $mark->value]);
            }
        }

        $category_2->attributes()->whereNotIn('name',
            [
                'Момент инерции',
                'Упругий момент сопротивления',
                'Статический момент',
                'Пластический момент сопротивления',
                'Площадь сечения',
                'Высота h',
                'Вес 1 м.п.',
                'Толщина боковых стенок s',
                'Толщина полки t',
                'Ширина профиля по центрам замков b',
                'Марка',
            ])->update(['is_display' => 0]);

        // create ManualReference with parameters based on exist materials
        foreach ($marks->unique() as $index => $item) {
            dump($index . ' in ' . $marks->unique()->count());

            $newReference = ManualReference::create([
                'name' => 'Шпунт ' . $item['value'],
                'category_id' => $category_2->id,
            ]);

            $material = ManualMaterial::with('parameters')
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
                        'Момент инерции',
                        'Упругий момент сопротивления',
                        'Статический момент',
                        'Пластический момент сопротивления',
                        'Площадь сечения',
                        'Высота h',
                        'Вес 1 м.п.',
                        'Толщина боковых стенок s',
                        'Толщина полки t',
                        'Ширина профиля по центрам замков b',
                        'Марка',
                    ]))
                {
                    $newReference->parameters()->create([
                        'attr_id' => $parameter->attr_id,
                        'value' => $parameter->value,
                    ]);
                }
            }
        }

        // create ManualReference with parameters based on exist materials
        foreach ($markOther->unique() as $index => $item) {
            dump($index . ' in ' . $marks->unique()->count());

            $newReference = ManualReference::create([
                'name' => 'Трубошпунт ' . $item['value'],
                'category_id' => $category_2->id,
            ]);

            $material = ManualMaterial::with('parameters')
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
                        'Момент инерции',
                        'Упругий момент сопротивления',
                        'Статический момент',
                        'Пластический момент сопротивления',
                        'Площадь сечения',
                        'Высота h',
                        'Вес 1 м.п.',
                        'Толщина боковых стенок s',
                        'Толщина полки t',
                        'Ширина профиля по центрам замков b',
                        'Марка',
                    ]))
                {
                    $newReference->parameters()->create([
                        'attr_id' => $parameter->attr_id,
                        'value' => $parameter->value,
                    ]);
                } {
                    $newReference->parameters()->create([
                        'attr_id' => $parameter->attr_id,
                        'value' => $parameter->value,
                        'is_display' => 0,
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
        $manualReferences = ManualReference::where('category_id', 2)->get();

        foreach ($manualReferences as $index => $manualReference) {
            $manualReference->parameters()->delete();
            $manualReference->delete();
        }


    }
}
