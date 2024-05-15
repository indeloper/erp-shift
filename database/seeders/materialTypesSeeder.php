<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class materialTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('q3w_material_types')->insert([
            'id' => 1,
            'name' => 'Шпунт',
            'measure_unit' => 1,
            'accounting_type' => 2,
            'description' => 'Шпунт измеряется по короткой ликвидной части, длина округляется до ближайшего кратного метру значения, но не более чем на 100 мм. Если более 100 мм, записывается точное значение, до мм',
        ]);

        DB::table('q3w_material_types')->insert([
            'id' => 2,
            'name' => 'Арматура',
            'measure_unit' => 1,
            'accounting_type' => 1,
            'description' => 'Классификация и сортамент арматуры по ГОСТ 5181-82',
        ]);

        DB::table('q3w_material_types')->insert([
            'id' => 3,
            'name' => 'Двутавр',
            'measure_unit' => 1,
            'accounting_type' => 1,
            'description' => 'Двутавр широкополочный по ГОСТ 57837-2017, Двутавр колонный (К) по ГОСТ 57837-2017, Двутавр нормальный (Б) по ГОСТ 57837-2017',
        ]);

        DB::table('q3w_material_types')->insert([
            'id' => 4,
            'name' => 'Труба прямошовная',
            'measure_unit' => 1,
            'accounting_type' => 1,
            'description' => 'Трубы электросварные прямошовные по ГОСТ 10704-91',
        ]);

        DB::table('q3w_material_types')->insert([
            'id' => 5,
            'name' => 'Швеллер',
            'measure_unit' => 1,
            'accounting_type' => 1,
            'description' => 'Швеллеры с параллельными гранями полок по ГОСТ 8240-89, швеллеры с уклоном полок по ГОСТ 8240-89',
        ]);

        DB::table('q3w_material_types')->insert([
            'id' => 6,
            'name' => 'Угловой элемент',
            'measure_unit' => 1,
            'accounting_type' => 2,
            'description' => '',
        ]);

        DB::table('q3w_material_types')->insert([
            'id' => 7,
            'name' => 'Лист г/к',
            'measure_unit' => 2,
            'accounting_type' => 1,
            'description' => '',
        ]);

        DB::table('q3w_material_types')->insert([
            'id' => 8,
            'name' => 'Труба квадратная',
            'measure_unit' => 1,
            'accounting_type' => 2,
            'description' => 'Трубы стальные квадратные по ГОСТ 8639-82',
        ]);
    }
}
