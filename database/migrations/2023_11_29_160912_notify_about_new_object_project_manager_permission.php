<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class NotifyAboutNewObjectProjectManagerPermission extends Migration
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
            'name' => 'Объекты: получение уведомлений о назначении для объектов руководителей проектов',
            'codename' => 'notify_about_new_object_project_manager',
            'category' => 4,
        ],
    ];
}
