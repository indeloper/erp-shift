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
        Schema::create('project_object_document_types', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентфикатор');
            $table->integer('sortOrder')->comment('Порядок сортировки документов');
            $table->string('name')->comment('Наименование');

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE project_object_document_types COMMENT 'Типы документов в модуле «Документооборот на объектах»'");

        $this->uploadData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_object_document_types');
    }

    public function uploadData()
    {
        DB::table('project_object_document_types')->insert([
            ['name' => 'РД', 'sortOrder' => 10], // 1
            ['name' => 'Акт с площадки', 'sortOrder' => 30], // 2
            ['name' => 'Журнал', 'sortOrder' => 40], // 3
            ['name' => 'ППР', 'sortOrder' => 20], // 4
            ['name' => 'ИД', 'sortOrder' => 50], // 5
            ['name' => 'Выполнение', 'sortOrder' => 60], // 6
            ['name' => 'Прочее', 'sortOrder' => 70], // 7
        ]);
    }
};
