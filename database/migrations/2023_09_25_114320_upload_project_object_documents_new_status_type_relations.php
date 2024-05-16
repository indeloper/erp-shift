<?php

use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UploadProjectObjectDocumentsNewStatusTypeRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->uploadData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('project_object_document_status_type_relations')
            ->where('document_status_id', ProjectObjectDocumentStatus::where('name', 'Хранится на площадке')->first()->id)
            ->delete();

        DB::table('project_object_document_statuses')
            ->where([
                'name' => 'Хранится на площадке',
            ])->delete();
    }

    public function uploadData()
    {
        $newStatus = ProjectObjectDocumentStatus::create(
            ['name' => 'Хранится на площадке', 'status_type_id' => 2, 'sortOrder' => 45]
        );

        DB::table('project_object_document_status_type_relations')->insert([
            ['document_type_id' => 1, 'document_status_id' => $newStatus->id, 'default_selection' => false],
            ['document_type_id' => 4, 'document_status_id' => $newStatus->id, 'default_selection' => false],
        ]);
    }
}
