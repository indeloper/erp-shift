<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_object_document_statuses', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентфикатор');
            $table->string('name')->comment('Наименование');
            $table->string('style');
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement("ALTER TABLE project_object_document_statuses COMMENT 'Статусы документов в модуле «Документооборот на объектах»'");

        $this->uploadData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_object_document_statuses');
    }

    public function uploadData()
    {
        DB::table('project_object_document_statuses')->insert([
            ['name' => 'Не оформлен', 'style' => '#dd5e5e'], // 1
            ['name' => 'Не получен', 'style' => '#dd5e5e'], // 2
            ['name' => 'В работе', 'style' => '#dd5e5e'], // 3

            ['name' => 'На площадке', 'style' => '#ffcd72'], // 4
            ['name' => 'Ведется, на площадке', 'style' => '#ffcd72'], // 5
            // ['name' => 'Подписан, на площадке', 'style' => '#ffcd72'], // бывш 6 - Объединен с id-4
            ['name' => 'Оформлен и готов к передаче', 'style' => '#ffcd72'], // 7 -> 6
            ['name' => 'Передан заказчику', 'style' => '#ffcd72'], // 8 -> 7
            ['name' => 'Передан в офис', 'style' => '#ffcd72'], // 9 -> 8

            ['name' => 'Получен офисом', 'style' => '#1f931f'], // 10 -> 9
        ]);
    }
};
