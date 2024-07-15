<?php

use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        $category = ManualMaterialCategory::find(5);

        $attr_1 = $category->attributes()->whereUnit('кг')->first();
        $attr_2 = $category->attributes()->whereName('Удельная площадь')->first();

        foreach ($category->materials as $material) {
            if (isset($material->parameters()->whereAttrId($attr_2->id)->first()->value)) {
                $material->parameters()->create([
                    'value' => (1 / (float) str_replace(',', '.', (($material->parameters()->whereAttrId($attr_2->id)->first()->value) ?? 0)) * 1000),
                    'attr_id' => $attr_1->id,
                ]);
            }
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
