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

        $category_tongue = ManualMaterialCategory::find(2);
        $category_tongue->category_unit = 'шт';

        $paramters_count_change = $category_tongue->attributes()->where('name', 'like', '%'.'Удельное количество'.'%')->first();
        $paramters_length_change = $category_tongue->attributes()->where('name', 'like', '%'.'Удельный погонаж'.'%')->first();
        $paramters_length_change_round = $category_tongue->attributes()->where('name', 'like', '%'.'Длина'.'%')->first();
        $paramters_weight_one_meter = $category_tongue->attributes()->where('name', 'like', '%'.'Вес 1 м.п'.'%')->first();

        foreach ($category_tongue->materials as $material) {
            $param_meter_weight = $material->parameters()->where('attr_id', $paramters_weight_one_meter->id)->first();
            $param_weigth = $material->parameters()->where('attr_id', $paramters_count_change->id)->first();
            $param_length = $material->parameters()->where('attr_id', $paramters_length_change->id)->first();
            $param_length_round = $material->parameters()->where('attr_id', $paramters_length_change_round->id)->first();

            $param_weigth->value = 1 / $param_weigth->value;
            $param_length->value = $param_length_round->value;
            $param_meter_weight->value = ($param_weigth->value / $param_length->value) * 1000;

            $param_length->save();
            $param_weigth->save();
            $param_meter_weight->save();
        }

        $paramters_count_change->name = 'Удельный тоннаж';
        $paramters_count_change->unit = 'т';
        $paramters_count_change->save();

        $category_tongue->save();

        // dd('_____');
        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::beginTransaction();

        $category_tongue = ManualMaterialCategory::find(2);
        $category_tongue->category_unit = 'т';

        $paramters_weight_change = $category_tongue->attributes()->where('name', 'like', '%'.'Удельный тоннаж'.'%')->first();
        $paramters_length_change = $category_tongue->attributes()->where('name', 'like', '%'.'Удельный погонаж'.'%')->first();
        // dd($paramters_weight_change, $paramters_length_change);
        foreach ($category_tongue->materials as $material) {
            $param_weigth = $material->parameters()->where('attr_id', $paramters_weight_change->id)->first();
            $param_length = $material->parameters()->where('attr_id', $paramters_length_change->id)->first();

            $param_weigth->value = 1 / $param_weigth->value;
            $param_length->value = $param_weigth->value * $param_length->value;
            // dd($param_weigth->value, $param_length->value);

            $param_length->save();
            $param_weigth->save();
        }

        $paramters_weight_change->name = 'Удельное количество';
        $paramters_weight_change->unit = 'шт';
        $paramters_weight_change->save();

        $category_tongue->save();

        DB::commit();
    }
};
