<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class materialTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("q3w_material_types")->insert([
            "name" => "Шпунт",
            "measure_unit" => 4,
            "accounting_type" => 2,
            "description" => "Шпунт измеряется по короткой ликвидной части, длина округляется до ближайшего кратного метру значения, но не более чем на 100 мм. Если более 100 мм, записывается точное значение, до мм",
        ]);

        DB::table("q3w_material_types")->insert([
            "name" => "Арматура",
            "measure_unit" => 4,
            "accounting_type" => 1,
            "description" => "Классификация и сортамент арматуры по ГОСТ 5181-82",
        ]);

        DB::table("q3w_material_types")->insert([
            "name" => "Двутавр",
            "measure_unit" => 4,
            "accounting_type" => 1,
            "description" => "Двутавр широкополочный по ГОСТ 26020-83, двутавр колонный (К) по ГОСТ 26020-83, двутавр нормальный (Б) по ГОСТ 26020-83",
        ]);

        DB::table("q3w_material_types")->insert([
            "name" => "Труба прямошовная",
            "measure_unit" => 4,
            "accounting_type" => 1,
            "description" => "Трубы электросварные прямошовные по ГОСТ 10704-91",
        ]);

        DB::table("q3w_material_types")->insert([
            "name" => "Швеллер",
            "measure_unit" => 4,
            "accounting_type" => 1,
            "description" => "Швеллеры с параллельными гранями полок по ГОСТ 8240-89, швеллеры с углоном полок по ГОСТ 8240-89",
        ]);
    }
}
