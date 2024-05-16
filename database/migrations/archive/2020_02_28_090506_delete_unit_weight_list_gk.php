<?php

use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
     */
    public function down(): void
    {
        //
    }
};
