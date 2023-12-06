<?php

use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLengthToCategoryFormula extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $categories = ManualMaterialCategory::find([4, 6, 7, 8, 9, 11]);

        foreach ($categories as $category) {
            $attr_id = $category->attributes()->where('name', 'Длина')->first()->id;
            $category->update(['formula' => $category->formula . ' ' .'<attr>' . $attr_id . '</attr>' . ' метров']);
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
        // no way
    }
}
