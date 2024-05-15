<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('groups')->insert([
            ['id' => 1, 'name' => 'Бухгалтер', 'department_id' => '2'],
            ['id' => 2, 'name' => 'Геодезист', 'department_id' => '10'],
            ['id' => 3, 'name' => 'Генеральный директор', 'department_id' => '1'],
            ['id' => 4, 'name' => 'Главный бухгалтер', 'department_id' => '2'],
            ['id' => 5, 'name' => 'Главный инженер', 'department_id' => '10'],
            ['id' => 6, 'name' => 'Главный механик', 'department_id' => '11'],
            ['id' => 7, 'name' => 'Директор по развитию', 'department_id' => '6'],
            ['id' => 8, 'name' => 'Заведующий складом', 'department_id' => '3'],
            ['id' => 9, 'name' => 'Инженер ПТО', 'department_id' => '9'],
            ['id' => 10, 'name' => 'Кладовщик', 'department_id' => '3'],
            ['id' => 11, 'name' => 'Машинист копра', 'department_id' => '3'],
            ['id' => 12, 'name' => 'Машинист крана', 'department_id' => '10'],
            ['id' => 13, 'name' => 'Машинист крана', 'department_id' => '11'],
            ['id' => 14, 'name' => 'Менеджер по подбору персонала', 'department_id' => '5'],
            ['id' => 15, 'name' => 'Менеджер по техническому надзору', 'department_id' => '4'],
            ['id' => 16, 'name' => 'Начальник ПТО', 'department_id' => '9'],
            ['id' => 17, 'name' => 'Проектировщик', 'department_id' => '8'],
            ['id' => 18, 'name' => 'Производитель работ', 'department_id' => '10'],
            ['id' => 20, 'name' => 'Секретарь', 'department_id' => '1'],
            ['id' => 22, 'name' => 'Стропальщик', 'department_id' => '3'],
            ['id' => 23, 'name' => 'Стропальщик', 'department_id' => '10'],
            ['id' => 24, 'name' => 'Заместитель генерального директора', 'department_id' => '1'],
            ['id' => 25, 'name' => 'Финансовый директор', 'department_id' => '2'],
            ['id' => 26, 'name' => 'Экономист по договорной работе', 'department_id' => '7'],
            ['id' => 27, 'name' => 'Экономист по МТО', 'department_id' => '3'],
            ['id' => 28, 'name' => 'Электрогазосварщик', 'department_id' => '3'],
            ['id' => 29, 'name' => 'Электрогазосварщик', 'department_id' => '10'],
            ['id' => 30, 'name' => 'Электросварщик', 'department_id' => '3'],
            ['id' => 31, 'name' => 'Электрослесарь по ремонту оборудования', 'department_id' => '11'],
            ['id' => 32, 'name' => 'Юрист', 'department_id' => '7'],
            ['id' => 33, 'name' => 'Руководитель проектов (сваи)', 'department_id' => '10'],
            ['id' => 34, 'name' => 'Руководитель проектов (шпунт)', 'department_id' => '10'],
            ['id' => 35, 'name' => 'Специалист по продажам и ведению клиентов (сваи)', 'department_id' => '6'],
            ['id' => 36, 'name' => 'Специалист по продажам и ведению клиентов (шпунт)', 'department_id' => '6'],

        ]);
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
