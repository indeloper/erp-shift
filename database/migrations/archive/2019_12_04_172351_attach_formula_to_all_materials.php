<?php

use App\Models\Manual\ManualMaterialCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::beginTransaction();

        $category = ManualMaterialCategory::find(2);
        // <cat>name</cat> <attr>158</attr> <attr>27</attr> метров <attr>156</attr>
        $attr_mark = $category->attributes()->where('name', 'like', '%'.'марка'.'%')->first();
        $attr_analogs = $category->attributes()->where('name', 'like', '%'.'Аналоги'.'%')->first();
        $attr_length = $category->attributes()->where('name', 'like', '%'.'Длина'.'%')->first();

        $category->formula = '<cat>name</cat> <attr>'.$attr_mark->id.'</attr> <attr>'.$attr_length->id.'</attr> метров <attr>'.$attr_analogs->id.'</attr>';
        $category->save();

        $category = ManualMaterialCategory::find(3);
        // Арматура периодич. 25Г2С d28мм (11,7)
        $attr_1 = $category->attributes()->where('name', 'like', '%'.'Марка'.'%')->first();
        $attr_2 = $category->attributes()->where('name', 'like', '%'.'Диаметр'.'%')->first();
        $attr_3 = $category->attributes()->where('name', 'like', '%'.'Длина'.'%')->first();

        $category->formula = 'Арматура периодич. <attr>'.$attr_1->id.'</attr> d<attr>'.$attr_2->id.'</attr>мм';
        $category->save();

        $category = ManualMaterialCategory::find(4);
        // Балка 30Ш2
        $attr_1 = $category->attributes()->where('name', 'like', '%'.'Марка'.'%')->first();

        $category->formula = '<cat>name</cat> <attr>'.$attr_1->id.'</attr>';
        $category->save();

        $category = ManualMaterialCategory::find(5);
        // Лист г/к - 16х360х190
        $attr_1 = $category->attributes()->where('name', 'like', '%'.'Толщина'.'%')->first();
        $attr_2 = $category->attributes()->where('name', 'like', '%'.'Длина'.'%')->first();
        $attr_3 = $category->attributes()->where('name', 'like', '%'.'Ширина'.'%')->first();

        $category->formula = '<cat>name</cat> <attr>'.$attr_1->id.'</attr>х'.'<attr>'.$attr_2->id.'</attr>х'.'<attr>'.$attr_3->id.'</attr>';
        $category->save();

        $category = ManualMaterialCategory::find(6);
        // Швеллер 40П /2
        $attr_1 = $category->attributes()->where('name', 'like', '%'.'Высота h'.'%')->first();
        $attr_2 = $category->attributes()->where('name', 'like', '%'.'Серия'.'%')->first();

        $category->formula = '<cat>name</cat> '.'<attr>'.$attr_1->id.'/10'.'</attr>'.'<attr>'.$attr_2->id.'</attr>';
        $category->save();

        $category = ManualMaterialCategory::find(7);
        // Труба 1020*10
        $attr_1 = $category->attributes()->where('name', 'like', '%'.'Диаметр'.'%')->first();
        $attr_2 = $category->attributes()->where('name', 'like', '%'.'Толщина стенки'.'%')->first();

        $category->formula = 'Труба '.'<attr>'.$attr_1->id.'</attr>'.'*'.'<attr>'.$attr_2->id.'</attr>';
        $category->save();

        $category = ManualMaterialCategory::find(8);
        // Труба 89*10
        $attr_1 = $category->attributes()->where('name', 'like', '%'.'Диаметр'.'%')->first();
        $attr_2 = $category->attributes()->where('name', 'like', '%'.'Толщина стенки'.'%')->first();

        $category->formula = 'Труба '.'<attr>'.$attr_1->id.'</attr>'.'*'.'<attr>'.$attr_2->id.'</attr>';
        $category->save();

        $category = ManualMaterialCategory::find(9);
        // Труба профильная 50*50*4
        $attr_1 = $category->attributes()->where('name', 'like', '%'.'Толщина'.'%')->first();
        $attr_2 = $category->attributes()->where('name', 'like', '%'.'Длина стороны а'.'%')->first();
        $attr_3 = $category->attributes()->where('name', 'like', '%'.'Длина стороны б'.'%')->first();

        $category->formula = 'Труба профильная '.'<attr>'.$attr_2->id.'</attr>'.'*'.'<attr>'.$attr_3->id.'</attr>'.'*'.'<attr>'.$attr_1->id.'</attr>';
        $category->save();

        $category = ManualMaterialCategory::find(10);
        // Угловой элемент E22 (LV22/C9)
        $attr_1 = $category->attributes()->where('name', 'like', '%'.'Марка'.'%')->first();
        $attr_2 = $category->attributes()->where('name', 'like', '%'.'Аналоги'.'%')->first();

        $category->formula = '<cat>name</cat> '.'<attr>'.$attr_1->id.'</attr> '.'<attr>'.$attr_2->id.'</attr>';
        $category->save();

        $category = ManualMaterialCategory::find(11);
        // Уголок горячекатанный 200*16 мм Ст3ПС/СП5
        $attr_1 = $category->attributes()->where('name', 'like', '%'.'Ширина'.'%')->first();
        $attr_2 = $category->attributes()->where('name', 'like', '%'.'Толщина'.'%')->first();
        $attr_3 = $category->attributes()->where('name', 'like', '%'.'Материал'.'%')->first();

        $category->formula = 'Уголок горячекатанный '.'<attr>'.$attr_1->id.'</attr>'.'*'.'<attr>'.$attr_2->id.'</attr>'.' мм '.'<attr>'.$attr_3->id.'</attr>';
        $category->save();

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {

    }
};
