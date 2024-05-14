<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTechnicMovementNotificationsPermissionsToPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')->insert($this->getNewEntries());
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

    public function getNewEntries()
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
            'name' => 'Техника: получение уведомлений о перемещении негабаритной техники',
            'codename' => 'technics_movement_receive_oversized_order_notification',
            'category' => 13,
        ],
        [
            'name' => 'Техника: получение уведомлений о перемещении габаритной техники',
            'codename' => 'technics_movement_receive_standard_size_notification',
            'category' => 13,
        ],
    ];
}
