<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTechicMovementResponsiblePermissions extends Migration
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
        foreach (self::NEW_ENTRIES as $newEntry) {
            DB::table('permissions')->where('codename', $newEntry['codename'])->delete();
        }
    }

    public function getNewEntrises()
    {
        $newEntries = self::NEW_ENTRIES;
        foreach ($newEntries as $key => $newEntry) {
            $newEntries[$key]['created_at'] = now();
            $newEntries[$key]['updated_at'] = now();
        }

        return $newEntries;
    }

    const NEW_ENTRIES = [
        [
            'name' => 'Техника: обработка заявок на перемещение негабаритной техники',
            'codename' => 'technics_processing_movement_oversized_equipment',
            'category' => 13,
        ],
        [
            'name' => 'Техника: обработка заявок на перемещение габаритной техники',
            'codename' => 'technics_processing_movement_standart_sized_equipment',
            'category' => 13,
        ],
    ];
}
