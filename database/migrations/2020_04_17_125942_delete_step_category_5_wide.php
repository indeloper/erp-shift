<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Manual\ManualMaterialCategory;

class DeleteStepCategory5Wide extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $category = ManualMaterialCategory::find(5);
        $category->load('attributes');
        $attr = $category->attributes()->where('name', 'Толщина')->first();
        $attr->step = 1;
        $attr->save();

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

        $category = ManualMaterialCategory::find(5);
        $category->load('attributes');
        $attr = $category->attributes()->where('name', 'Толщина')->first();
        $attr->step = 2;
        $attr->save();

        DB::commit();
    }
}
