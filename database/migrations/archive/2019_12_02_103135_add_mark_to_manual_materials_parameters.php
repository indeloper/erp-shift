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

        $category_tongue = ManualMaterialCategory::find(2);
        $category_tongue->attributes()->create([
            'name' => 'Аналоги',
            'description' => 'Аналоги',
            'is_required' => 0,
            'unit' => '',
            'is_preset' => 0,
        ]);

        $category_tongue->attributes()->create([
            'name' => 'Клиновидный',
            'description' => 'Является ли шпунт клиновидным',
            'is_required' => 1,
            'unit' => '',
            'is_preset' => 0,
        ]);

        $category_tongue->attributes()->create([
            'name' => 'Трубошпунт',
            'description' => 'Является ли шпунт трубошпунтом',
            'is_required' => 1,
            'unit' => '',
            'is_preset' => 0,
        ]);

        $category_tongue->attributes()->create([
            'name' => 'Марка',
            'description' => 'Марка',
            'is_required' => 1,
            'unit' => '',
            'is_preset' => 0,
        ]);

        $category_beam = ManualMaterialCategory::find(4);
        $category_beam->attributes()->create([
            'name' => 'Марка',
            'description' => 'Марка',
            'is_required' => 1,
            'unit' => '',
            'is_preset' => 0,
        ]);

        $category_sheet = ManualMaterialCategory::find(5);
        $category_sheet->attributes()->create([
            'name' => 'Толщина',
            'description' => 'Толщина листа',
            'is_required' => 1,
            'unit' => 'мм',
            'is_preset' => 0,
            'step' => 2,
            'from' => 2,
            'to' => 100,
        ]);

        $category_sheet->attributes()->create([
            'name' => 'Длина',
            'description' => 'Длина листа',
            'is_required' => 0,
            'unit' => 'мм',
            'is_preset' => 0,
            'step' => 10,
            'from' => 100,
            'to' => 50000,
        ]);

        $category_sheet->attributes()->create([
            'name' => 'Ширина',
            'description' => 'Ширина листа',
            'is_required' => 0,
            'unit' => 'мм',
            'is_preset' => 0,
            'step' => 10,
            'from' => 100,
            'to' => 50000,
        ]);

        $category_channel = ManualMaterialCategory::find(6);
        $category_channel->attributes()->create([
            'name' => 'Серия',
            'description' => 'Серия швеллеров',
            'is_required' => 1,
            'unit' => '',
            'is_preset' => 0,
        ]);

        $category_angle = ManualMaterialCategory::find(10);
        $category_angle->attributes()->create([
            'name' => 'Марка',
            'description' => 'Марка',
            'is_required' => 1,
            'unit' => '',
            'is_preset' => 0,
        ]);

        $category_angle->attributes()->create([
            'name' => 'Аналоги',
            'description' => 'Аналоги',
            'is_required' => 0,
            'unit' => '',
            'is_preset' => 0,
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $category_tongue = ManualMaterialCategory::find(2);
        $category_tongue->attributes()->where('name', 'Клиновидный')->delete();
        $category_tongue->attributes()->where('name', 'Марка')->delete();
        $category_tongue->attributes()->where('name', 'Аналоги')->delete();

        $category_beam = ManualMaterialCategory::find(4);
        $category_beam->attributes()->where('name', 'Марка')->delete();

        $category_sheet = ManualMaterialCategory::find(5);
        $category_sheet->attributes()->where('name', 'Толщина')->delete();
        $category_sheet->attributes()->where('name', 'Длина')->delete();
        $category_sheet->attributes()->where('name', 'Ширина')->delete();

        $category_channel = ManualMaterialCategory::find(6);
        $category_channel->attributes()->where('name', 'Серия')->delete();

        $category_angle = ManualMaterialCategory::find(10);
        $category_angle->attributes()->where('name', 'Марка')->delete();
        $category_angle->attributes()->where('name', 'Аналоги')->delete();
    }
};
