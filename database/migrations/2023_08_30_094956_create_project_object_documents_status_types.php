<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProjectObjectDocumentsStatusTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_object_documents_status_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('Наименование');
            $table->string('slug')->index()->comment('Кодовое наименование');
            $table->integer('sortOrder')->comment('Порядок сортировки');
            $table->string('style')->comment('Цветовая маркировка');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE project_object_documents_status_types COMMENT 'Типы статусов документов в модуле «Документооборот на объектах»'");
        $this->uploadData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_object_documents_status_types');
    }

    public function uploadData()
    {
        DB::table('project_object_documents_status_types')->insert([
            [
                'name' => 'Работа с документом не начата',
                'slug' => 'work_with_document_not_started',
                'sortOrder' => 10,
                'style' => '#dd5e5e',
            ],
            [
                'name' => 'Работа с документом ведется',
                'slug' => 'work_with_document_in_progress',
                'sortOrder' => 20,
                'style' => '#ffcd72',
            ],
            [
                'name' => 'Работа с документом завершена',
                'slug' => 'work_with_document_is_finished',
                'sortOrder' => 30,
                'style' => '#1f931f',
            ],
            [
                'name' => 'Документ в архиве или удален',
                'slug' => 'document_archived_or_deleted',
                'sortOrder' => 40,
                'style' => '#c5c7c5',
            ],
        ]);
    }
}
