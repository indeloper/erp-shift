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

        $category = ManualMaterialCategory::find(10);

        $new_attribute = $category->attributes()->create([
            'name' => 'Масса 1 м.п.',
            'description' => 'Масса 1 м.п.',
            'is_required' => 0,
            'unit' => 'кг',
            'is_preset' => 0,
        ]);

        foreach ($category->materials as $material) {
            $material->parameters()->create([
                'attr_id' => $new_attribute->id,
                'value' => (1 / $material->convert_to('м.п')->value) * 1000,
            ]);
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
