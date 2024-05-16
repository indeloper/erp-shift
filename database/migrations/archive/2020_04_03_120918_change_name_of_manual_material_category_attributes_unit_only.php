<?php

use App\Models\Manual\ManualMaterialCategoryAttribute;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeNameOfManualMaterialCategoryAttributesUnitOnly extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    const ATTR_NEW_NAME = [
        'Удельный тоннаж' => 'Масса 1 ',
        'Удельный погонаж' => 'Длина 1 ',
        'Удельное количество' => 'Количество в 1 ',
        'Удельная масса' => 'Масса 1 ',
        'Удельная площадь' => 'Площадь 1 ',
    ];

    public function up()
    {
        DB::beginTransaction();

        $attributes = ManualMaterialCategoryAttribute::with('category')->where('name', 'like', '%удельн%')->get();

        foreach ($attributes as $attribute) {
            $attribute->name = self::ATTR_NEW_NAME[$attribute->name].($attribute->category->category_unit ?? 'т');
            $attribute->save();
        }

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // no way
    }
}
