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
        $category = ManualMaterialCategory::with('materials')->where('id', 5)->first();

        $attr_new = $category->attributes()->create([
            'name' => 'Удельный тоннаж',
            'description' => 'Кол-во тонн в штуке',
            'is_required' => 1,
            'unit' => 'т',
            'is_preset' => 1,
        ]);

        foreach ($category->materials as $material) {
            $weight_per_square_meter = (float) str_replace(',', '.', $material->parameters()->where('name', 'Удельная площадь')->first()->value ?? 0);
            $square_meter = (float) str_replace(',', '.', $material->parameters()->where('name', 'Масса 1 м2')->first()->value ?? 0);

            $material->parameters()->create([
                'attr_id' => $attr_new->id,
                'value' => (($weight_per_square_meter * $square_meter) / 1000),
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
