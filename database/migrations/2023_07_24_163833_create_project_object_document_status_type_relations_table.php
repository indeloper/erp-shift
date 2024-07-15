<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_object_document_status_type_relations', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентфикатор');

            $table->unsignedBigInteger('document_status_id')->comment('ID статуса документа');
            $table->foreign('document_status_id', 'project_object_document_status_foreign')->references('id')->on('project_object_document_statuses');

            $table->unsignedBigInteger('document_type_id')->comment('ID типа документа');
            $table->foreign('document_type_id', 'project_object_document_type_foreign')->references('id')->on('project_object_document_types');

            $table->boolean('default_selection')->nullable()->comment('Статус при создании');

            $table->timestamps();
        });

        DB::statement("ALTER TABLE project_object_document_status_type_relations COMMENT 'Связи типов и статусов в модуле «Документооборот на объектах»'");

        $this->uploadData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_object_document_status_type_relations');
    }

    public function uploadData()
    {
        DB::table('project_object_document_status_type_relations')->insert([
            ['document_type_id' => 1, 'document_status_id' => 2, 'default_selection' => true],
            ['document_type_id' => 1, 'document_status_id' => 9, 'default_selection' => false],

            ['document_type_id' => 2, 'document_status_id' => 1, 'default_selection' => true],
            ['document_type_id' => 2, 'document_status_id' => 4, 'default_selection' => false],
            ['document_type_id' => 2, 'document_status_id' => 8, 'default_selection' => false],
            ['document_type_id' => 2, 'document_status_id' => 9, 'default_selection' => false],

            ['document_type_id' => 3, 'document_status_id' => 1, 'default_selection' => true],
            ['document_type_id' => 3, 'document_status_id' => 5, 'default_selection' => false],
            ['document_type_id' => 3, 'document_status_id' => 8, 'default_selection' => false],
            ['document_type_id' => 3, 'document_status_id' => 9, 'default_selection' => false],

            ['document_type_id' => 4, 'document_status_id' => 3, 'default_selection' => true],
            ['document_type_id' => 4, 'document_status_id' => 9, 'default_selection' => false],

            ['document_type_id' => 5, 'document_status_id' => 6, 'default_selection' => true],
            ['document_type_id' => 5, 'document_status_id' => 7, 'default_selection' => false],
            ['document_type_id' => 5, 'document_status_id' => 4, 'default_selection' => false],
            ['document_type_id' => 5, 'document_status_id' => 8, 'default_selection' => false],
            ['document_type_id' => 5, 'document_status_id' => 9, 'default_selection' => false],

            ['document_type_id' => 6, 'document_status_id' => 6, 'default_selection' => true],
            ['document_type_id' => 6, 'document_status_id' => 7, 'default_selection' => false],
            ['document_type_id' => 6, 'document_status_id' => 8, 'default_selection' => false],
            ['document_type_id' => 6, 'document_status_id' => 9, 'default_selection' => false],

            ['document_type_id' => 7, 'document_status_id' => 4, 'default_selection' => true],
            ['document_type_id' => 7, 'document_status_id' => 9, 'default_selection' => false],
        ]);
    }
};
