<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TableProjectObjectDocumentStatusesChangeTableAndAddData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_object_document_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('status_type_id')->after('id')->comment('ID типа статуса');
            $table->integer('sortOrder')->after('status_type_id')->comment('Порядок сортировки');
        });

        $this->uploadData();

        Schema::table('project_object_document_statuses', function (Blueprint $table) {
            $table->foreign('status_type_id')->references('id')->on('project_object_documents_status_types');
        });

        Schema::table('project_object_document_statuses', function (Blueprint $table) {
            $table->dropColumn('style');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_object_document_statuses', function (Blueprint $table) {
            $table->dropForeign('project_object_document_statuses_status_type_id_foreign');
            $table->dropColumn('status_type_id');
            $table->dropColumn('sortOrder');
        });

        DB::table('project_object_document_statuses')
            ->where('name', 'В архиве')
            ->orWhere('name', 'Удален')
            ->delete();

        Schema::table('project_object_document_statuses', function (Blueprint $table) {
            $table->string('style');
        });
    }

    public function uploadData()
    {
        DB::table('project_object_document_statuses')->insert([
            [
                'name' => 'В архиве',
                'style' => '#c5c7c5',
            ],
            [
                'name' => 'Удален',
                'style' => '#c5c7c5',
            ],
        ]);

        DB::table('project_object_document_statuses')->where('style', '#dd5e5e')->update([
            'status_type_id' => 1,
        ]);

        DB::table('project_object_document_statuses')->where('style', '#ffcd72')->update([
            'status_type_id' => 2,
        ]);

        DB::table('project_object_document_statuses')->where('style', '#1f931f')->update([
            'status_type_id' => 3,
        ]);

        DB::table('project_object_document_statuses')->where('style', '#c5c7c5')->update([
            'status_type_id' => 4,
        ]);

        $statusesIds = DB::table('project_object_document_statuses')->pluck('id');

        foreach ($statusesIds as $statusId) {
            DB::table('project_object_document_statuses')->where('id', $statusId)->update([
                'sortOrder' => $statusId * 10,
            ]);
        }

    }
}
