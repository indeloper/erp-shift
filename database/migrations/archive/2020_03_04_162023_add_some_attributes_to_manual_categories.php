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
        $category = ManualMaterialCategory::find(3);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->create([
            'name' => 'Вес 1 м.п.',
            'description' => 'Вес одного метра погонного',
            'is_required' => 1,
            'unit' => 'кг',
            'is_preset' => 0,
            'is_display' => 1,
        ]);

        $category = ManualMaterialCategory::find(6);
        $category->load('materials.parameters', 'attributes');

        $category->attributesAll()->where('name', 'Удельный погонаж')->update(['is_display' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $category = ManualMaterialCategory::find(3);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Вес 1 м.п.')->delete();

        $category = ManualMaterialCategory::find(6);
        $category->load('materials.parameters', 'attributes');

        $category->attributesAll()->where('name', 'Удельный погонаж')->update(['is_display' => 1]);
    }
};
