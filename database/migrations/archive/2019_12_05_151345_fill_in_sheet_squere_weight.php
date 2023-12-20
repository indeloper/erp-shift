<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Manual\ManualMaterialCategory;

use Illuminate\Support\Facades\DB;

class FillInSheetSquereWeight extends Migration
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

        $attr_1 = $category->attributes()->whereUnit('кг')->first();
        $attr_2 = $category->attributes()->whereName('Удельная площадь')->first();

        foreach ($category->materials as $material) {
            if (isset($material->parameters()->whereAttrId($attr_2->id)->first()->value)) {
                $material->parameters()->create([
                    'value' => (1 / (float)str_replace(',','.', (($material->parameters()->whereAttrId($attr_2->id)->first()->value) ?? 0)) * 1000),
                    'attr_id' => $attr_1->id
                ]);
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
