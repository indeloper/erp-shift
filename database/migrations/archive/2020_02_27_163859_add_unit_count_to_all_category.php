<?php

use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $categories = ManualMaterialCategory::find([3, 4, 5, 6, 7, 8, 9, 11, 16]);

        foreach ($categories as $category) {
            $category->attributesAll()->create([
                'name' => 'Удельное количество',
                'description' => 'Количество штук в одной единицы измерения категории',
                'is_required' => 0,
                'unit' => 'шт',
                'is_preset' => 1,
                'is_display' => 0,
            ]);
        }

        $categories = ManualMaterialCategory::find([4, 6, 7, 8, 9, 11, 16]);

        foreach ($categories as $category) {
            $category->attributesAll()->create([
                'name' => 'Длина',
                'description' => 'Длина материала',
                'is_required' => 0,
                'unit' => 'м',
                'is_preset' => 0,
                'is_display' => 1,
            ]);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $categories = ManualMaterialCategory::find([3, 4, 5, 6, 7, 8, 9, 11, 16]);

        foreach ($categories as $category) {
            $category->attributesAll()->where('name', 'Удельное количество')->delete();
        }

        $categories = ManualMaterialCategory::find([4, 6, 7, 8, 9, 11, 16]);

        foreach ($categories as $category) {
            $category->attributesAll()->where('name', 'Длина')->delete();
        }
    }
};
