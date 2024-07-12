<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaterialOperationReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('q3w_material_operation_reasons')->insert($this->getNewEntries(self::NEW_REASONS));
    }

    const NEW_REASONS = [
        [
            'operation_route_id' => 1,
            'name' => 'Покупка'
        ],[
            'operation_route_id' => 1,
            'name' => 'Ошибочный возврат'
        ],[
            'operation_route_id' => 1,
            'name' => 'Инвентаризация'
        ],[
            'operation_route_id' => 2,
            'name' => 'Обратный выкуп'
        ],[
            'operation_route_id' => 2,
            'name' => 'Между объектами или на склад'
        ],[
            'operation_route_id' => 2,
            'name' => 'Поставка внешний поставщик/ ООО СК Город база фиктивный'
        ],[
            'operation_route_id' => 4,
            'name' => 'Признание конструктивным'
        ],[
            'operation_route_id' => 4,
            'name' => 'Использован для работ'
        ],[
            'operation_route_id' => 4,
            'name' => 'Ошибочная поставка'
        ],[
            'operation_route_id' => 4,
            'name' => 'Технологические потери при резке торцовке приравнивании не более 3%'
        ],[
            'operation_route_id' => 4,
            'name' => 'Кража'
        ]
    ];

    public function getNewEntries(array $newEntries): array
    {
        foreach ($newEntries as $key => $values) {
            $newEntries[$key]['created_at'] = now();
            $newEntries[$key]['updated_at'] = now();
        }

        return $newEntries;
    }
}
