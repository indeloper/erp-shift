<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFuelTanksPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')->insert($this->getNewEntrises());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach(self::NEW_ENTRIES as $newEntry) {
            DB::table('permissions')->where('codename', $newEntry['codename'])->delete();
        }
    }

    public function getNewEntrises() {
        $newEntries = self::NEW_ENTRIES;
        foreach($newEntries as $key=>$newEntry) {
            $newEntries[$key]['created_at'] = now();
            $newEntries[$key]['updated_at'] = now();
        }
        return $newEntries;
    }

    const NEW_ENTRIES = [
        // Топливные емкости
        [
            'name' => 'Топливные емкости: доступ к разделу', 
            'codename' => 'fuel_tanks_access',
            'category' => 17,
        ],
        [
            'name' => 'Топливные емкости: добавление', 
            'codename' => 'add_fuel_tanks',
            'category' => 17,
        ],
        [
            'name' => 'Топливные емкости: удаление', 
            'codename' => 'delete_fuel_tanks',
            'category' => 17,
        ],
        [
            'name' => 'Топливные емкости: редактирование', 
            'codename' => 'update_fuel_tanks',
            'category' => 17,
        ],
        [
            'name' => 'Топливные емкости: возможность видеть все емкости', 
            'codename' => 'watch_any_fuel_tanks',
            'category' => 17,
        ],

        // Топливные операции
        [
            'name' => 'Топливные операции: доступ к разделу', 
            'codename' => 'fuel_tank_flows_access',
            'category' => 17,
        ],
        [
            'name' => 'Топливные операции: возможность создавать корректировки', 
            'codename' => 'adjust_fuel_tank_remains',
            'category' => 17,
        ],
        [
            'name' => 'Топливные операции: возможность создавать записи для своих емкостей', 
            'codename' => 'create_fuel_tank_flows_for_reportable_tanks',
            'category' => 17,
        ],
        [
            'name' => 'Топливные операции: возможность создавать записи для всех емкостей', 
            'codename' => 'create_fuel_tank_flows_for_any_tank',
            'category' => 17,
        ],
        [
            'name' => 'Топливные операции: возможность видеть записи об операциях для всех емкостей', 
            'codename' => 'watch_any_fuel_tank_flows',
            'category' => 17,
        ],

        // Отчет по топливным операциям
        [
            'name' => 'Отчет по топливным операциям: расширенные настройки фильтра', 
            'codename' => 'fuel_tank_operations_report_advanced_filter_settings_access',
            'category' => 17,
        ],

        // Отчет о перемещениях топливных емкостей
        [
            'name' => 'Отчет о перемещениях топливных емкостей', 
            'codename' => 'fuel_tanks_movements_report_access',
            'category' => 17,
        ],
        
    ];
}
