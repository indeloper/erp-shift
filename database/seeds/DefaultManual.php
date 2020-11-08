<?php

use Illuminate\Database\Seeder;

use App\Models\Manual\ManualWork;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualMaterialCategoryAttribute;
use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialParameter;
class DefaultManual extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //заполняем работы
        factory(ManualWork::class)->create(['name' => 'Вибропогружение шпунта', 'work_group_id' => '1']);
        factory(ManualWork::class)->create(['name' => 'Виброизвлечение шпунта', 'work_group_id' => '1']);
        factory(ManualWork::class)->create(['name' => 'Доставка шпунта', 'work_group_id' => '1']);
        factory(ManualWork::class)->create(['name' => 'Доставка свай', 'work_group_id' => '2']);
        factory(ManualWork::class)->create(['name' => 'Устройство свайного поля', 'work_group_id' => '2']);
        factory(ManualWork::class)->create(['name' => 'Резание свай', 'work_group_id' => '2']);
        factory(ManualWork::class)->create(['name' => 'Глубокие раскопки', 'work_group_id' => '3']);
        factory(ManualWork::class)->create(['name' => 'Забор грунта', 'work_group_id' => '3']);
        factory(ManualWork::class)->create(['name' => 'Извлечение полезных ископаемых', 'work_group_id' => '3']);
        factory(ManualWork::class)->create(['name' => 'Крепление шпунта', 'work_group_id' => '4']);
        factory(ManualWork::class)->create(['name' => 'Крепление свай', 'work_group_id' => '4']);
        factory(ManualWork::class)->create(['name' => 'Расчёт количества шпунта', 'work_group_id' => '1']);
        factory(ManualWork::class)->create(['name' => 'Очистка шпунта', 'work_group_id' => '1']);
        factory(ManualWork::class)->create(['name' => 'Вколачивание шпунта', 'work_group_id' => '1']);
        factory(ManualWork::class)->create(['name' => 'Забор свай', 'work_group_id' => '2']);
        factory(ManualWork::class)->create(['name' => 'Разбор свайного поля', 'work_group_id' => '2']);
        factory(ManualWork::class)->create(['name' => 'Сварка свай', 'work_group_id' => '2']);
        factory(ManualWork::class)->create(['name' => 'Малой глубины раскопки', 'work_group_id' => '3']);
        factory(ManualWork::class)->create(['name' => 'Забор глины', 'work_group_id' => '3']);
        factory(ManualWork::class)->create(['name' => 'Извлечение металла из земли', 'work_group_id' => '3']);
        factory(ManualWork::class)->create(['name' => 'Крепление рабочих материалов', 'work_group_id' => '4']);
        factory(ManualWork::class)->create(['name' => 'Крепление расходников', 'work_group_id' => '4']);

        //заполняем категории
        factory(ManualMaterialCategory::class)->create(['name' => 'Шпунт']);
        factory(ManualMaterialCategory::class)->create(['name' => 'Сваи']);
        factory(ManualMaterialCategory::class)->create(['name' => 'Крепления']);

        //добавляем им атрибуты
        factory(ManualMaterialCategoryAttribute::class, 12)->create();

        //создаем материалы в наших категориях
        factory(ManualMaterial::class, 12)->create();

        //добавляем им параметры
        factory(ManualMaterialParameter::class, 12)->create();
    }
}
