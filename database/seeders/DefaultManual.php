<?php

namespace Database\Seeders;

use App\Models\Manual\ManualMaterial;
use App\Models\Manual\ManualMaterialCategory;
use App\Models\Manual\ManualMaterialCategoryAttribute;
use App\Models\Manual\ManualMaterialParameter;
use App\Models\Manual\ManualWork;
use Illuminate\Database\Seeder;

class DefaultManual extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //заполняем работы
        ManualWork::factory()->create(['name' => 'Вибропогружение шпунта', 'work_group_id' => '1']);
        ManualWork::factory()->create(['name' => 'Виброизвлечение шпунта', 'work_group_id' => '1']);
        ManualWork::factory()->create(['name' => 'Доставка шпунта', 'work_group_id' => '1']);
        ManualWork::factory()->create(['name' => 'Доставка свай', 'work_group_id' => '2']);
        ManualWork::factory()->create(['name' => 'Устройство свайного поля', 'work_group_id' => '2']);
        ManualWork::factory()->create(['name' => 'Резание свай', 'work_group_id' => '2']);
        ManualWork::factory()->create(['name' => 'Глубокие раскопки', 'work_group_id' => '3']);
        ManualWork::factory()->create(['name' => 'Забор грунта', 'work_group_id' => '3']);
        ManualWork::factory()->create(['name' => 'Извлечение полезных ископаемых', 'work_group_id' => '3']);
        ManualWork::factory()->create(['name' => 'Крепление шпунта', 'work_group_id' => '4']);
        ManualWork::factory()->create(['name' => 'Крепление свай', 'work_group_id' => '4']);
        ManualWork::factory()->create(['name' => 'Расчёт количества шпунта', 'work_group_id' => '1']);
        ManualWork::factory()->create(['name' => 'Очистка шпунта', 'work_group_id' => '1']);
        ManualWork::factory()->create(['name' => 'Вколачивание шпунта', 'work_group_id' => '1']);
        ManualWork::factory()->create(['name' => 'Забор свай', 'work_group_id' => '2']);
        ManualWork::factory()->create(['name' => 'Разбор свайного поля', 'work_group_id' => '2']);
        ManualWork::factory()->create(['name' => 'Сварка свай', 'work_group_id' => '2']);
        ManualWork::factory()->create(['name' => 'Малой глубины раскопки', 'work_group_id' => '3']);
        ManualWork::factory()->create(['name' => 'Забор глины', 'work_group_id' => '3']);
        ManualWork::factory()->create(['name' => 'Извлечение металла из земли', 'work_group_id' => '3']);
        ManualWork::factory()->create(['name' => 'Крепление рабочих материалов', 'work_group_id' => '4']);
        ManualWork::factory()->create(['name' => 'Крепление расходников', 'work_group_id' => '4']);

        //заполняем категории
        ManualMaterialCategory::factory()->create(['name' => 'Шпунт']);
        ManualMaterialCategory::factory()->create(['name' => 'Сваи']);
        ManualMaterialCategory::factory()->create(['name' => 'Крепления']);

        //добавляем им атрибуты
        ManualMaterialCategoryAttribute::factory()->count(12)->create();

        //создаем материалы в наших категориях
        ManualMaterial::factory()->count(12)->create();

        //добавляем им параметры
        ManualMaterialParameter::factory()->count(12)->create();
    }
}
