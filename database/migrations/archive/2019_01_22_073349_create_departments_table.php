<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
        });

        DB::table('departments')->insert([
            ["name" => 'Администрация'],
            ["name" => 'Бухгалтерия'],
            ["name" => 'Материально-технический'],
            ["name" => 'Отдел качества'],
            ["name" => 'Отдел персонала'],
            ["name" => 'Отдел продаж'],
            ["name" => 'Претензионно-договорной'],
            ["name" => 'Проектный'],
            ["name" => 'ПТО'],
            ["name" => 'Строительный'],
            ['name' => 'УМиТ']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('departments');
    }
}