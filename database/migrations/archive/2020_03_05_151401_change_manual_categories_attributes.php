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
        $category = ManualMaterialCategory::find(4);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Масса 1 м.п.')->update(['is_required' => 1]);

        $category = ManualMaterialCategory::find(5);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Масса 1 м2')->update(['is_required' => 1]);

        $category = ManualMaterialCategory::find(7);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Масса 1 м.п.')->update(['is_required' => 1]);

        $category = ManualMaterialCategory::find(8);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Масса 1 м.п.')->update(['is_required' => 1]);

        $category = ManualMaterialCategory::find(9);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Масса 1 м.п.')->update(['is_required' => 1]);

        $category = ManualMaterialCategory::find(10);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Масса 1 м.п.')->update(['is_required' => 1]);

        $category = ManualMaterialCategory::find(11);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->create([
            'name' => 'Вес 1 м.п.',
            'description' => 'Вес одного метра погонного',
            'is_required' => 1,
            'unit' => 'кг',
            'is_preset' => 0,
            'is_display' => 1,
        ]);

        $category->attributes()->where('name', 'Материал')->update(['name' => 'Марка стали']);
        $category->attributes()->where('name', 'Ширина полки')->update(['name' => 'Длина стороны а']);

        $category->attributes()->create([
            'name' => 'Длина стороны б',
            'description' => '',
            'is_required' => 1,
            'unit' => 'мм',
            'is_preset' => 0,
            'is_display' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $category = ManualMaterialCategory::find(4);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Масса 1 м.п.')->update(['is_required' => 0]);

        $category = ManualMaterialCategory::find(5);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Масса 1 м2')->update(['is_required' => 0]);

        $category = ManualMaterialCategory::find(7);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Масса 1 м.п.')->update(['is_required' => 0]);

        $category = ManualMaterialCategory::find(8);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Масса 1 м.п.')->update(['is_required' => 0]);

        $category = ManualMaterialCategory::find(9);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Масса 1 м.п.')->update(['is_required' => 0]);

        $category = ManualMaterialCategory::find(10);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Масса 1 м.п.')->update(['is_required' => 0]);

        $category = ManualMaterialCategory::find(11);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Вес 1 м.п.')->delete();

        $category->attributes()->where('name', 'Марка стали')->update(['name' => 'Материал']);
        $category->attributes()->where('name', 'Длина стороны а')->update(['name' => 'Ширина полки']);

        $category->attributes()->where('name', 'Длина стороны б')->delete();
    }
};
