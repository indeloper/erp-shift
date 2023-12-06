<?php

use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteUnitWeightListGk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $category = ManualMaterialCategory::find(5);

        $attr = $category->attributesAll()->where('name', 'like', '%Удельный тоннаж%')->first();
        $materials = $category->materials;

        foreach ($materials as $index => $material) {
            $material->parameters()->where('attr_id', $attr->id)->delete();
        }

        $category->attributesAll()->where('name', 'like', '%Удельный тоннаж%')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
