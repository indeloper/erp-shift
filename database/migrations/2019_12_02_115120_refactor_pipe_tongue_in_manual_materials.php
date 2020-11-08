<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Manual\ManualMaterialCategory;

use Illuminate\Support\Facades\DB;

class RefactorPipeTongueInManualMaterials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $category_tongue = ManualMaterialCategory::find(2);

        $attr_mark = $category_tongue->attributes()->where('name', 'like', '%' . 'марка' . '%')->first();

        foreach ($category_tongue->materials()->where('name', 'like', '%Трубошпунт%')->get() as $material) {
            $mark = '';
            $explode = explode(' ', $material->name);
            array_pop($explode);
            array_pop($explode);
            array_shift($explode);

            $mark = implode(' ', $explode);
            $material->parameters()->create([
                'attr_id' => $attr_mark->id,
                'value' => $mark
            ]);
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

        $category_tongue = ManualMaterialCategory::find(2);

        $attr_mark = $category_tongue->attributes()->where('name', 'like', '%' . 'марка' . '%')->first();

        foreach ($category_tongue->materials()->where('name', 'like', '%Трубошпунт%')->get() as $material) {
            $material->parameters()->where('attr_id', $attr_mark->id)->delete();
        }

        DB::commit();
    }
}
