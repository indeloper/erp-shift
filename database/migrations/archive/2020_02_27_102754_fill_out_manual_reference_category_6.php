<?php


use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualReference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillOutManualReferenceCategory6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $category = ManualMaterialCategory::find(6);
        $category->load('materials.parameters', 'attributes');
        $params = collect();

        foreach ($category->materials as $material) {
            $height = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%' . 'высота h' . '%');
            })->first();

            $type = $material->parameters()->whereHas('attribute', function ($q) {
                $q->where('name', 'like', '%' . 'серия' . '%');
            })->first();

            if (isset($height->value) && isset($type->value)) {
                $params->push([
                    ['attr_id' => $height->attr_id, 'value' => $height->value],
                    ['attr_id' => $type->attr_id, 'value' => $type->value],
                ]);
            }
        }

        $category->attributes()->whereNotIn('name',
            [
                'Площадь поперечного сечения',
                'Удельный погонаж',
                'Высота h',
                'Высота h',
                'Масса 1 м.п.',
                'Момент инерции Ix',
                'Момент сопротивления Wx',
                'Радиус закругления полки r',
                'Радиус кривизны R',
                'Растояние от оси Y-Y до наружной грани стенки',
                'Толщина полки t',
                'Толщина стенки s',
                'Ширина полки b',
                'Серия',
            ])->update(['is_display' => 0]);

        // create ManualReference with parameters based on exist materials
        foreach ($params->unique() as $index => $item) {
            dump( $index . ' in ' . $params->unique()->count());

            $newReference = ManualReference::create([
                'name' => 'Швеллер ' . mb_substr($item[0]['value'], 0, -1) . $item[1]['value'] ,
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
                        'Площадь поперечного сечения',
                        'Удельный погонаж',
                        'Высота h',
                        'Масса 1 м.п.',
                        'Момент инерции Ix',
                        'Момент сопротивления Wx',
                        'Радиус закругления полки r',
                        'Радиус кривизны R',
                        'Растояние от оси Y-Y до наружной грани стенки',
                        'Толщина полки t',
                        'Толщина стенки s',
                        'Ширина полки b',
                        'Серия',
                    ]))
                {
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

        $manualReferences = ManualReference::where('category_id', 6)->get();

        foreach ($manualReferences as $index => $manualReference) {
            $manualReference->parameters()->delete();
            $manualReference->delete();
        }
    }
}