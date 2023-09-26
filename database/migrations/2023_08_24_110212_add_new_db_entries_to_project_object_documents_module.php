<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddNewDbEntriesToProjectObjectDocumentsModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('project_object_document_status_type_relations')->insert([
            'document_status_id' => 2,
            'document_type_id' => 7
        ]);

        DB::table('permissions')->insert([
            'name' => 'Документооборот (Площадка ⇆ Офис): фильтрация по ответственному по умолчанию', 
            'codename' => 'project_object_documents_default_filtering_by_responsible_user',
            'category' => 20,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('project_object_document_status_type_relations')->where([
            'document_status_id' => 2,
            'document_type_id' => 7
        ])->delete();

        DB::table('permissions')->where([
            'name' => 'Документооборот (Площадка ⇆ Офис): фильтрация по ответственному по умолчанию', 
            'codename' => 'project_object_documents_default_filtering_by_responsible_user',
            'category' => 20,
        ])->delete();
    }
}
