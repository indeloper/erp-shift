<?php

use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualReference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorMaterialParametersToAllMaterials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        DB::beginTransaction();
//        $categories = ManualMaterialCategory::whereNotIn('id', [12, 14])->get();
//
//        foreach ($categories as $category) {
//            $materials = ManualMaterial::where('category_id', $category->id)->get();
//            dump('category ' . $category->id);
//            dump('not null ' . $materials->where('manual_reference_id', '!=', null)->count());
//            dump('null ' . $materials->where('manual_reference_id', null)->count());
//            dump('all ' . $materials->count());
//
//            foreach ($materials->where('manual_reference_id', null) as $material) {
//                dump($material->name);
//            }
//
//        }
//
//        dd('stop');
//        DB::commit();
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
