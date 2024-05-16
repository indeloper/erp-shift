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
        $category->attributes()->where('name', 'Длина')->update(['is_required' => 0]);

        $category = ManualMaterialCategory::find(6);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', '!=', 'Масса 1 м.п.')->update(['is_required' => 0]);
        $category->attributes()->where('name', 'Масса 1 м.п.')->update(['is_required' => 1]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $category = ManualMaterialCategory::find(3);
        $category->load('materials.parameters', 'attributes');
        $category->attributes()->where('name', 'Длина')->update(['is_required' => 1]);
    }
};
