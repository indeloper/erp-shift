<?php

use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatus;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentStatusOptions;
use App\Models\ProjectObjectDocuments\ProjectObjectDocumentType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddProjectObjectDocumentsStatusInfo234234 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(
            !DB::table('project_object_document_status_type_relations')
                ->where([
                    ['document_type_id', ProjectObjectDocumentType::where('name', 'Прочее')->first()->id],
                    ['document_status_id', ProjectObjectDocumentStatus::where('name', 'Не оформлен')->first()->id]
                ])->count()
        ) {
            DB::table('project_object_document_status_type_relations')->insert(
                [
                    'document_status_id' => ProjectObjectDocumentStatus::where('name', 'Не оформлен')->first()->id,
                    'document_type_id' => ProjectObjectDocumentType::where('name', 'Прочее')->first()->id,
                    'default_selection' => 1
                ]
            );
        }
        

        DB::table('project_object_document_status_type_relations')
            ->where([
                ['document_status_id', 4],
                ['document_type_id', 7]
            ])
            ->update(['default_selection' => 0]);

        foreach(ProjectObjectDocumentType::all() as $type) {
            if(
                !DB::table('project_object_document_status_type_relations')
                    ->where([
                        ['document_type_id', $type->id],
                        ['document_status_id', ProjectObjectDocumentStatus::where('name', 'Передан в офис')->first()->id]
                    ])->count()
            ) {
                DB::table('project_object_document_status_type_relations')
                    ->insert([
                        'document_type_id' => $type->id,
                        'document_status_id' => ProjectObjectDocumentStatus::where('name', 'Передан в офис')->first()->id
                    ]);
            } 
        }

        DB::table('project_object_document_status_options')->insert([
            [
                'document_type_id' => 1,
                'document_status_id' => 8,
                'options' => json_encode([
                    [
                        'type' => 'select',
                        'id' => 'rd_who_get',
                        'label' => 'Кому передан',
                        'source' => 'responsible_managers_and_pto'
                    ]
                ])
            ],
            [
                'document_type_id' => 4,
                'document_status_id' => 8,
                'options' => json_encode([
                    [
                        'type' => 'select',
                        'id' => 'ppr_who_get',
                        'label' => 'Кому передан',
                        'source' => 'responsible_managers_and_pto'
                    ]
                ])
            ],
            [
                'document_type_id' => 7,
                'document_status_id' => 8,
                'options' => json_encode([
                    [
                        'type' => 'select',
                        'id' => 'other_who_get',
                        'label' => 'Кому передан',
                        'source' => 'responsible_managers_and_pto'
                    ]
                ])
            ],
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('project_object_document_status_type_relations')
        ->where([
            ['document_status_id', ProjectObjectDocumentStatus::where('name', 'Не оформлен')->first()->id],
            ['document_type_id', ProjectObjectDocumentType::where('name', 'Прочее')->first()->id],
        ])
            ->delete();

        DB::table('project_object_document_status_type_relations')
            ->where([
                ['document_status_id', 4],
                ['document_type_id', 7]
            ])
            ->update(['default_selection' => 1]);

        DB::table('project_object_document_status_options')
            ->where([
                ['document_type_id', 1],
                ['document_status_id', 8]
            ])
            ->orWhere([
                ['document_type_id', 4],
                ['document_status_id', 8]
            ])
            ->orWhere([
                ['document_type_id', 7],
                ['document_status_id', 8]
            ])
                ->delete();
    }
}
