<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddNewPermissionsRelatedToProjectObjectDocuments extends Migration
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
            'name' => 'Документооборот (Площадка ⇆ Офис): просмотр списка документов между офисом и площадкой',
            'codename' => 'project_object_documents_access',
            'category' => 20,
        ],
        [
            'name' => 'Объекты: назначение ответственных за объект ПТО',
            'codename' => 'can_assign_responsible_pto_user',
            'category' => 4,
        ],
        [
            'name' => 'Объекты: назначение ответственных за объект прорабов',
            'codename' => 'can_assign_responsible_foreman_user',
            'category' => 4,
        ],
        [
            'name' => 'Объекты: назначение ответственных за объект руководителей проектов',
            'codename' => 'can_assign_responsible_projectManager_user',
            'category' => 4,
        ],
        [
            'name' => 'Документооборот (Площадка ⇆ Офис): установка финального статуса документам между офисом и площадкой',
            'codename' => 'can_setup_final_project_object_document_status',
            'category' => 20,
        ],
        [
            'name' => 'Документооборот (Площадка ⇆ Офис): удаление файлов к документам между офисом и площадкой',
            'codename' => 'can_delete_project_object_document_files',
            'category' => 20,
        ],
    ];
}
