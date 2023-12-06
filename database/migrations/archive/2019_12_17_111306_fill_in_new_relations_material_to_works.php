<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Manual\ManualMaterialCategoryRelationToWork;
use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Support\Facades\DB;


class FillInNewRelationsMaterialToWorks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();
        $categories = ManualMaterialCategory::with('materials')->whereNotIn('id', [12,13,14])->get();
        // related_works
        foreach ($categories as $category) {
            foreach ($category->materials as $material) {

                $count_related_works = $material->related_works()->count();

                foreach ($material->related_works as $key => $work) {
                    if (is_null($category->related_works()->where('manual_works.id', $work->id)->first())) {
                        ManualMaterialCategoryRelationToWork::create(['work_id' => $work->id, 'manual_material_category_id' => $category->id]);
                        dump('category ' . $category->name . ', work ' . $work->name . 'count: ' . ($key + 1) . '/' . $count_related_works);
                    } else {
                        dump('already exist');
                    }
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

    }
}
