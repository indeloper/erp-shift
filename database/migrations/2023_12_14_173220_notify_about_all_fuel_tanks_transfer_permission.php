<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class NotifyAboutAllFuelTanksTransferPermission extends Migration
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
        [
            'name' => 'Топливные емкости: получение уведомлений обо всех перемещениях топливных емкостей', 
            'codename' => 'notify_about_all_fuel_tanks_transfer',
            'category' => 17,
        ],
    ];
}