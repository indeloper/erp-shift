<?php

use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        $category = ManualMaterialCategory::find(10);
        // Угловой элемент E22 (LV22/C9)
        $attr_meter = $category->attributes()->whereUnit('м')->first();

        $category->formula .= ' <attr>'.$attr_meter->id.'</attr>'.' метров';
        $category->save();

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
};
