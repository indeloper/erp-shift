<?php

use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualMaterialCategoryAttribute;
use App\Models\Manual\ManualMaterialParameter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillCustomPiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $moldings = [6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16];
        $section = [30, 35, 40];
        $reinforcement = [2, 4, 6];
        $names_tail = ['-нсв', '-ссв', '-всв'];
        $stupids = ['Б', ''];
        $concrete = ['B20', 'B25', 'B30', 'B35'];
        $impermeability = ['W6', 'W8', 'W10', 'W12'];
        $frost_resistance = ['F50', 'F100', 'F150', 'F200'];


        $category_id = ManualMaterialCategory::where('name', 'Составные сваи')->pluck('id')->first();
        $molding_attr_id = ManualMaterialCategoryAttribute::where('name', 'Удельный погонаж')->where('category_id', $category_id)->pluck('id')->first();
        $section_attr_id = ManualMaterialCategoryAttribute::where('name', 'Сечение')->where('category_id', $category_id)->pluck('id')->first();
        $reinforcement_attr_id = ManualMaterialCategoryAttribute::where('name', 'Армирование')->where('category_id', $category_id)->pluck('id')->first();
        $lenght_attr_id = ManualMaterialCategoryAttribute::where('name', 'Длина')->where('category_id', $category_id)->pluck('id')->first();
        $concrete_attr_id = ManualMaterialCategoryAttribute::where('name', 'Бетон')->where('category_id', $category_id)->pluck('id')->first();
        $impermeability_attr_id = ManualMaterialCategoryAttribute::where('name', 'Морозостойкость')->where('category_id', $category_id)->pluck('id')->first();
        $frost_resistance_attr_id = ManualMaterialCategoryAttribute::where('name', 'Водонепроницаемость')->where('category_id', $category_id)->pluck('id')->first();
        $stupid_attr_id = ManualMaterialCategoryAttribute::where('name', 'Тупая')->where('category_id', $category_id)->pluck('id')->first();

        foreach ($moldings as $key => $molding) {
            foreach ($section as $key => $sec) {
                foreach ($reinforcement as $key => $reinf) {
                    foreach ($names_tail as $key => $tail_name) {
                        foreach ($stupids as $key => $stupid) {
                            foreach ($concrete as $key => $concr) {
                                foreach ($impermeability as $key => $imperm) {
                                    foreach ($frost_resistance as $key => $frost) {
                                        DB::beginTransaction();

                                        if ($tail_name == '-нсв') {
                                            $material = new ManualMaterial();
                                            $material->name = 'С' . $molding * 10 . '.' . $sec . $tail_name . $reinf . $stupid . '(' . $concr . $imperm . $frost . ')';
                                            $material->buy_cost = 0;
                                            $material->use_cost = 0;
                                            $material->category_id = $category_id;
                                            $material->save();

                                            $frost_resistance_attr = new ManualMaterialParameter();
                                            $frost_resistance_attr->attr_id = $stupid_attr_id;
                                            $frost_resistance_attr->value = $stupid == 'Б' ? 'Да' : 'Нет';
                                            $frost_resistance_attr->mat_id = (string) $material->id;
                                            $frost_resistance_attr->save();
                                        } else {
                                            $material = new ManualMaterial();
                                            $material->name = 'С' . $molding * 10 . '.' . $sec . $tail_name . $reinf . '(' . $concr . $imperm . $frost . ')';
                                            $material->buy_cost = 0;
                                            $material->use_cost = 0;
                                            $material->category_id = $category_id;
                                            $material->save();

                                            $frost_resistance_attr = new ManualMaterialParameter();
                                            $frost_resistance_attr->attr_id = $stupid_attr_id;
                                            $frost_resistance_attr->value = 'Нет';
                                            $frost_resistance_attr->mat_id = (string) $material->id;
                                            $frost_resistance_attr->save();
                                        }

                                        $molding_attr = new ManualMaterialParameter();
                                        $molding_attr->attr_id = $molding_attr_id;
                                        $molding_attr->value = $molding;
                                        $molding_attr->mat_id = (string) $material->id;
                                        $molding_attr->save();

                                        $section_attr = new ManualMaterialParameter();
                                        $section_attr->attr_id = $section_attr_id;
                                        $section_attr->value = $sec;
                                        $section_attr->mat_id = (string) $material->id;
                                        $section_attr->save();

                                        $reinforcement_attr = new ManualMaterialParameter();
                                        $reinforcement_attr->attr_id = $reinforcement_attr_id;
                                        $reinforcement_attr->value = $reinf;
                                        $reinforcement_attr->mat_id = (string) $material->id;
                                        $reinforcement_attr->save();

                                        $lenghtA_attr = new ManualMaterialParameter();
                                        $lenghtA_attr->attr_id = $lenght_attr_id;
                                        $lenghtA_attr->value = $molding;
                                        $lenghtA_attr->mat_id = (string) $material->id;
                                        $lenghtA_attr->save();

                                        $concrete_attr = new ManualMaterialParameter();
                                        $concrete_attr->attr_id = $concrete_attr_id;
                                        $concrete_attr->value = $concr;
                                        $concrete_attr->mat_id = (string) $material->id;
                                        $concrete_attr->save();

                                        $impermeability_attr = new ManualMaterialParameter();
                                        $impermeability_attr->attr_id = $impermeability_attr_id;
                                        $impermeability_attr->value = $imperm;
                                        $impermeability_attr->mat_id = (string) $material->id;
                                        $impermeability_attr->save();

                                        $frost_resistance_attr = new ManualMaterialParameter();
                                        $frost_resistance_attr->attr_id = $frost_resistance_attr_id;
                                        $frost_resistance_attr->value = $frost;
                                        $frost_resistance_attr->mat_id = (string) $material->id;
                                        $frost_resistance_attr->save();

                                        DB::commit();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}