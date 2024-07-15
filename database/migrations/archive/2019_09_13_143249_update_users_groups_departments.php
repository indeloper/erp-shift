<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        // work with departments, used in controllers/views = [5,6,7]
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // truncate departments table
        DB::table('departments')->truncate();

        // was
        $old_departments = [
            ['id' => 1, 'name' => 'Администрация'],
            ['id' => 2, 'name' => 'Бухгалтерия'],
            ['id' => 3, 'name' => 'Материально-технический'],
            ['id' => 4, 'name' => 'Отдел качества'],
            ['id' => 5, 'name' => 'Отдел персонала'],
            ['id' => 6, 'name' => 'Отдел продаж'],
            ['id' => 7, 'name' => 'Претензионно-договорной'],
            ['id' => 8, 'name' => 'Проектный'],
            ['id' => 9, 'name' => 'ПТО'],
            ['id' => 10, 'name' => 'Строительный'],
            ['id' => 11, 'name' => 'УМиТ'],
        ];

        // will
        DB::table('departments')->insert([
            // administration department
            ['id' => 1, 'name' => 'Администрация'],
            ['id' => 2, 'name' => 'Административно-хозяйственный отдел'],
            ['id' => 3, 'name' => 'Бухгалтерия'],
            ['id' => 4, 'name' => 'Дирекция'],
            ['id' => 5, 'name' => 'Отдел персонала'],
            ['id' => 6, 'name' => 'Финансовый отдел'],
            // construction department
            ['id' => 7, 'name' => 'Общестроительное направление'],
            ['id' => 8, 'name' => 'ОТМС и логистики'],
            ['id' => 9, 'name' => 'Лаборатория неразрушающего контроля'],
            ['id' => 10, 'name' => 'Свайное направление'],
            ['id' => 11, 'name' => 'Шпунтовое направление'],
            ['id' => 12, 'name' => 'Склад'],
            ['id' => 13, 'name' => 'УМиТ'],
            // technical department
            ['id' => 14, 'name' => 'Коммерческий отдел'],
            ['id' => 15, 'name' => 'Проектный отдел'],
            ['id' => 16, 'name' => 'ПТО'],
        ]);

        /*  !!! NOT actual !!!  */
        // styles key => new key or value
        $department_diffs = [
            // 1 => Администрация unchanged
            // 2 => new department -> Административно-технический отдел
            // Бухгалтерия moved
            2 => 3,
            // 3 => Материально-технический changed to Бухгалтерия
            // 4 => Отдел качества changed to Дирекция
            // 5 => Отдел персонала unchanged
            // 6 => Отдел продаж changed to Финансовый отдел
            // 7 => Претензионно-договорной changed to Дорожное направление
            // 8 => Проектный changed to ОТМС и логистики
            // Проектный moved
            8 => 15,
            // 9 => ПТО changed to ОСК
            // ПТО moved
            9 => 16,
            // 10 => Строительный changed to Свайное направление
            // 11 => new department -> Шпунтовое направление
            // УМиТ moved
            11 => 13,
            // 12 => new department -> Склад
            // 13 => УМиТ
            // 14 => new department -> Коммерческий отдел
            // 15 => Проектный отдел
            // 16 => ПТО
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // work with groups table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // truncate groups table
        DB::table('groups')->truncate();

        // was
        $old_groups = [
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
        ];

        // will
        DB::table('groups')->insert([
            // administration
            // administration department
            ['id' => 1, 'name' => 'Специалист по управленческому учёту', 'department_id' => '1'],
            // АХО department
            ['id' => 2, 'name' => 'Уборщица', 'department_id' => '2'],
            //  Бухгалтерия department
            ['id' => 3, 'name' => 'Бухгалтер', 'department_id' => '3'],
            ['id' => 4, 'name' => 'Главный бухгалтер', 'department_id' => '3'],
            //  Дирекция department
            ['id' => 5, 'name' => 'Генеральный директор', 'department_id' => '4'],
            ['id' => 6, 'name' => 'Заместитель генерального директора', 'department_id' => '4'],
            ['id' => 7, 'name' => 'Секретарь руководителя', 'department_id' => '4'],
            ['id' => 8, 'name' => 'Главный инженер', 'department_id' => '4'],
            ['id' => 9, 'name' => 'Архивариус', 'department_id' => '4'],
            // Отдел персонала department
            ['id' => 10, 'name' => 'Менеджер по персоналу', 'department_id' => '5'],
            ['id' => 11, 'name' => 'Инженер по охране труда', 'department_id' => '5'],
            // Финансовый отдел department
            ['id' => 12, 'name' => 'Финансовый директор', 'department_id' => '6'],
            // construction
            // Общестроительный department
            ['id' => 13, 'name' => 'Руководитель проектов (общестроительное направление)', 'department_id' => '7'],
            ['id' => 14, 'name' => 'Производитель работ (общестроительное направление)', 'department_id' => '7'],
            // ОТМС и логистики department
            ['id' => 15, 'name' => 'Экономист по материально-техническому снабжению', 'department_id' => '8'],
            ['id' => 16, 'name' => 'Агент по снабжению', 'department_id' => '8'],
            ['id' => 17, 'name' => 'Специалист по логистике', 'department_id' => '8'],
            // Лаборатория неразрушающего контроля department
            ['id' => 18, 'name' => 'Начальник лаборатории неразрушающего контроля', 'department_id' => '9'],
            // Свайное направление department
            ['id' => 19, 'name' => 'Руководитель проектов (свайное направление)', 'department_id' => '10'],
            ['id' => 20, 'name' => 'Электрогазосварщик (свайное направление)', 'department_id' => '10'],
            ['id' => 21, 'name' => 'Электросварщик (свайное направление)', 'department_id' => '10'],
            ['id' => 22, 'name' => 'Машинист крана (свайное направление)', 'department_id' => '10'],
            ['id' => 23, 'name' => 'Производитель работ (свайное направление)', 'department_id' => '10'],
            ['id' => 24, 'name' => 'Стропальщик (свайное направление)', 'department_id' => '10'],
            ['id' => 25, 'name' => 'Машинистр копра', 'department_id' => '10'],
            ['id' => 26, 'name' => 'Геодезист (свайное направление)', 'department_id' => '10'],
            // Шпунтовое направление department
            ['id' => 27, 'name' => 'Руководитель проектов (шпунтовое направление)', 'department_id' => '11'],
            ['id' => 28, 'name' => 'Электрогазосварщик (шпунтовое направление)', 'department_id' => '11'],
            ['id' => 29, 'name' => 'Электросварщик (шпунтовое направление)', 'department_id' => '11'],
            ['id' => 30, 'name' => 'Машинист крана (шпунтовое направление)', 'department_id' => '11'],
            ['id' => 31, 'name' => 'Производитель работ (шпунтовое направление)', 'department_id' => '11'],
            ['id' => 32, 'name' => 'Стропальщик (шпунтовое направление)', 'department_id' => '11'],
            ['id' => 33, 'name' => 'Копровщик', 'department_id' => '11'],
            ['id' => 34, 'name' => 'Геодезист (шпунтовое направление)', 'department_id' => '11'],
            ['id' => 35, 'name' => 'Мастер строительно-монтажных работ', 'department_id' => '11'],
            ['id' => 36, 'name' => 'Начальник участка', 'department_id' => '11'],
            ['id' => 37, 'name' => 'Техник', 'department_id' => '11'],
            ['id' => 38, 'name' => 'Подсобный рабочий', 'department_id' => '11'],
            // Склад department
            ['id' => 39, 'name' => 'Электрогазосварщик (склад)', 'department_id' => '12'],
            ['id' => 40, 'name' => 'Электросварщик (склад)', 'department_id' => '12'],
            ['id' => 41, 'name' => 'Машинист крана (склад)', 'department_id' => '12'],
            ['id' => 42, 'name' => 'Стропальщик (склад)', 'department_id' => '12'],
            ['id' => 43, 'name' => 'Заведующий складом', 'department_id' => '12'],
            ['id' => 44, 'name' => 'Кладовщик', 'department_id' => '12'],
            ['id' => 45, 'name' => 'Начальник производства', 'department_id' => '12'],
            // УМиТ department
            ['id' => 46, 'name' => 'Механик', 'department_id' => '13'],
            ['id' => 47, 'name' => 'Главный механик', 'department_id' => '13'],
            ['id' => 48, 'name' => 'Электрослесарь по ремонту электрооборудования', 'department_id' => '13'],
            // technical
            // Коммерческий отдел department
            ['id' => 49, 'name' => 'Юрист', 'department_id' => '14'],
            ['id' => 50, 'name' => 'Директор по развитию', 'department_id' => '14'],
            // Проектный отдел department
            ['id' => 51, 'name' => 'Инженер-проектировщик', 'department_id' => '15'],
            // ПТО department
            ['id' => 52, 'name' => 'Инженер ПТО', 'department_id' => '16'],
            ['id' => 53, 'name' => 'Начальник ПТО', 'department_id' => '16'],
            ['id' => 54, 'name' => 'Экономист по договорной и претензионной работе', 'department_id' => '16'],
        ]);

        /*  !!! NOT actual !!!  */
        // 'styles key|department_id' => 'new key or value|department_id'
        $group_diffs = [
            // administration department
            // 1|1 => new group -> Специалист по управленческому учёту
            // AXO department
            // Бухгалтер moved
            '1|2' => '3|3',
            // 2|2 => new group -> Уборщица
            // Геодезист separated in 25|10 (свая), 33|11 (шпунт)
            // Бухгалтерия department
            // 3|3 => Бухгалтер
            // Генеральный директор moved
            '3|1' => '5|4',
            // 4|3 => Главный бухгалтер
            '4|2' => '4|3',
            // Дирекция department
            // 5|4 => Генеральный директор
            // Главный инженер moved
            '5|10' => '7|4',
            // 6|4 => new group -> Секретарь руководителя
            // Главный механик moved
            '6|11' => '46|13',
            // 7|4 => Главный инженер
            // Директор по развитию moved
            '7|6' => '49|13',
            // 8|4 => new group -> Архивариус
            // Заведующий складом moved
            '8|3' => '42|12',
            // Отдел персонала department
            // 9|5 => Менеджер по персоналу
            // Инженер ПТО moved
            '9|9' => '51|16',
            // 10|5 => new group -> Инженер по охране труда
            // Кладовщик moved
            '10|3' => '43|12',
            // Финансовый отдел department
            // 11|6 => Финансовый директор
            // Машинист копра moved
            '11|3' => '24|10',
            // Дорожный department
            // 12|7 => Руководитель проектов (дорожное направление)
            // Машинист крана 12|10 separated to 21|10 (свая), 29|11 (шпунт), 40|12 (склад)
            // 13|7 => Производитель работ (дорожное направление)
            // Машинист крана 13|11 separated to 21|10 (свая), 29|11 (шпунт), 40|12 (склад)
            // ОТМС и логистики department
            // 14|8 => Экономист по материально-техническому снабжению
            // Менеджер по подбору персонала moved
            '14|5' => '9|5',
            // 15|8 => new group -> Агент по снабжению
            // Менеджер по техническому надзору changed to Агент по снабжению
            '15|4' => '15|8',
            // 16|8 => new group -> Специалист по логистике
            // Начальник ПТО moved
            '16|9' => '52|16',
            // ОСК department
            // 17|9 => new group -> Инженер по строительному контролю
            // Проектировщик moved
            '17|8' => '50|15',
            // Свайное направление department
            // 18|10 => Руководитель проектов (свая)
            // Производитель работ separated to 13|7 (дорожный), 22|10 (свая), 30|11 (шпунт)
            // 19|10 => Электрогазосварщик (свая)
            // 20|10 => Электросварщик (свая)
            // Секретарь moved
            '20|1' => '6|4',
            // 21|10 => Машинист крана (свая)
            // 22|10 => Производитель работ (свая)
            // Стропальщик 22|3 separated to 23|10 (свая), 31|11 (шпунт), 41|12 (склад)
            // 23|10 => Стропальщик (свая)
            // Стропальщик 23|10 separated to 23|10 (свая), 31|11 (шпунт), 41|12 (склад)
            // 24|10 => Машинист копра
            // Заместитель генерального директора changed to Машинист копра
            // 25|10 => Геодезист (свая)
            // Финансовый директор moved
            '25|2' => '11|6',
            // Шпунтовое направление department
            // 26|11 => Руководитель проектов (шпнут)
            // Экономист по договорной работе moved
            '26|7' => '53|16',
            // 27|11 => Электрогазосварщий (шпунт)
            // Экономист по МТО changed to Электрогазосварщик (шпунт)
            // 28|11 => Электросварщик (шпунт)
            // Электрогазосварщик 28|3 separated to 19|10 (свая), 27|11 (шпунт), 38|12 (склад)
            // 29|11 => Машинист крана (шпунт)
            // Электрогазосварщик 27|3 separated to 19|10 (свая), 27|11 (шпунт), 38|12 (склад)
            // 30|11 => Производитель работ (шпунт)
            // Электросварщик 30|3 separated to 20|10 (свая), 28|11 (шпунт), 39|12 (склад)
            // 31|11 => Стропальщик (шпунт)
            // Электрослесарь по ремонту оборудования moved
            '31|11' => '47|13',
            // 32|11 => Копровщик
            // Юрист moved
            '32|7' => '48|14',
            // 33|11 => Геодезист (шпунт)
            // Руководитель проектов (свая) moved
            '33|10' => '18|10',
            // 34|11 => Мастер строительно-монтажных работ
            // Руководитель проектов (шпунт) moved
            '34|10' => '26|11',
            // 35|11 => Начальник участка
            // Специалист по продажам и ведению клиентов (сваи) changed to Начальник участка
            // 36|11 => Техник
            // Специалист по продажам и ведению клиентов (шпунт) changed to Техник
            // 37|11 => Подсобный рабочий
            // Склад department
            // 38|12 => Электрогазосварщик (склад)
            // 39|12 => Электросварщик (склад)
            // 40|12 => Машинист крана (склад)
            // 41|12 => Стропальщик (склад)
            // 42|12 => Заведеющий складом
            // 43|12 => Кладовщик
            // 44|12 => Начальник производства
            // УМиТ department
            // 45|13 => Механик
            // 46|13 => Главный механик
            // 47|13 => Электрослесарь по ремонту оборудования
            // Коммерческий отдел department
            // 48|14 => Юрист
            // 49|14 => Директор по развитию
            // Проектный отдел department
            // 50|15 => Инженер-проектировщик
            // ПТО department
            // 51|16 => Инженер ПТО
            // 52|16 => Начальник ПТО
            // 53|16 => Экономист по договорной и претензионной работе
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // update users
        User::find(1)->update(['group_id' => 9, 'department_id' => 14]);
        User::find(6)->update(['group_id' => 5, 'department_id' => 4]);
        User::find(7)->update(['group_id' => 6, 'department_id' => 4]);
        User::find(8)->update(['group_id' => 7, 'department_id' => 4]);
        User::find(9)->update(['group_id' => 50, 'department_id' => 14]);
        User::find(10)->update(['group_id' => 27, 'department_id' => 11]);
        User::find(11)->update(['group_id' => 27, 'department_id' => 11]);
        User::find(12)->update(['group_id' => 19, 'department_id' => 10]);
        User::find(13)->update(['group_id' => 8, 'department_id' => 4]);
        User::find(14)->update(['group_id' => 27, 'department_id' => 11]);
        User::find(15)->update(['group_id' => 13, 'department_id' => 7]);
        User::find(16)->update(['is_deleted' => 1, 'status' => 0]);
        User::find(17)->update(['group_id' => 52, 'department_id' => 16]);
        User::find(18)->update(['is_deleted' => 1, 'status' => 0]);
        User::find(19)->update(['group_id' => 52, 'department_id' => 16]);
        User::find(20)->update(['group_id' => 52, 'department_id' => 16]);
        User::find(21)->update(['group_id' => 52, 'department_id' => 16]);
        User::find(22)->update(['group_id' => 53, 'department_id' => 16]);
        User::find(23)->update(['is_deleted' => 1, 'status' => 0]);
        User::find(24)->update(['group_id' => 52, 'department_id' => 16]);
        User::find(25)->update(['group_id' => 52, 'department_id' => 16]);
        User::find(26)->update(['group_id' => 49, 'department_id' => 14]);
        User::find(27)->update(['group_id' => 54, 'department_id' => 16]);
        User::find(28)->update(['group_id' => 54, 'department_id' => 16]);
        User::find(29)->update(['group_id' => 15, 'department_id' => 8]);
        User::find(30)->update(['group_id' => 51, 'department_id' => 15]);
        User::find(31)->update(['group_id' => 51, 'department_id' => 15]);
        User::find(32)->update(['group_id' => 18, 'department_id' => 9]);
        User::find(33)->update(['group_id' => 31, 'department_id' => 11]);
        User::find(34)->update(['group_id' => 31, 'department_id' => 11]);
        User::find(35)->update(['group_id' => 31, 'department_id' => 11]);
        User::find(36)->update(['group_id' => 31, 'department_id' => 11]);
        User::find(37)->update(['group_id' => 31, 'department_id' => 11]);
        User::find(38)->update(['group_id' => 31, 'department_id' => 11]);
        User::find(39)->update(['group_id' => 31, 'department_id' => 11]);
        User::find(40)->update(['group_id' => 31, 'department_id' => 11]);
        User::find(41)->update(['group_id' => 31, 'department_id' => 11]);
        User::find(43)->update(['group_id' => 23, 'department_id' => 10]);
        User::find(44)->update(['group_id' => 23, 'department_id' => 10]);
        User::find(45)->update(['group_id' => 36, 'department_id' => 11]);
        User::find(46)->update(['group_id' => 35, 'department_id' => 11]);
        User::find(47)->update(['group_id' => 31, 'department_id' => 11]);
        User::find(48)->update(['group_id' => 31, 'department_id' => 11]);
        User::find(49)->update(['group_id' => 23, 'department_id' => 10]);
        User::find(50)->update(['group_id' => 23, 'department_id' => 10]);
        User::find(51)->update(['group_id' => 10, 'department_id' => 5]);
        User::find(52)->update(['group_id' => 52, 'department_id' => 16]);
        User::find(53)->update(['is_deleted' => 1, 'status' => 0]);
        // special for demo
        $optional = User::find(54);
        !$optional ?: $optional->update(['group_id' => 52, 'department_id' => 16]);
        $optional = User::find(55);
        !$optional ?: $optional->update(['group_id' => 52, 'department_id' => 16]);
        $optional = User::find(56);
        !$optional ?: $optional->update(['group_id' => 17, 'department_id' => 8]);
        $optional = User::find(57);
        !$optional ?: $optional->update(['group_id' => 52, 'department_id' => 16]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // rest
    }
};
