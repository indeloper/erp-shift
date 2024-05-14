<?php

use App\Models\Comment;
use App\Models\FileEntry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProjectObjectDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_object_documents', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентфикатор');

            $table->unsignedBigInteger('document_type_id')->comment('ID типа документа');
            $table->foreign('document_type_id')->references('id')->on('project_object_document_types');

            $table->unsignedBigInteger('document_status_id')->comment('ID статуса документа');
            $table->foreign('document_status_id')->references('id')->on('project_object_document_statuses');

            $table->unsignedInteger('project_object_id')->comment('ID объекта');
            $table->foreign('project_object_id')->references('id')->on('project_objects');

            $table->json('options')->nullable()->comment('Параметры дополнительные');

            $table->unsignedInteger('author_id')->comment('ID автора');
            $table->foreign('author_id')->references('id')->on('users');

            $table->string('document_name')->comment('Наименование документа');
            $table->date('document_date')->nullable()->comment('Дата документа');

            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement("ALTER TABLE project_object_documents COMMENT 'Документы в модуле «Документооборот на объектах»'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_object_documents');

        Comment::where('commentable_type', 'App\Models\ProjectObjectDocuments\ProjectObjectDocument')->delete();
        FileEntry::where('documentable_type', 'App\Models\ProjectObjectDocuments\ProjectObjectDocument')->delete();
    }
}
