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
        Schema::create('fuel_tank_flow_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('Наименование');
            $table->string('slug')->comment('Кодовое наименование');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE fuel_tank_flow_types COMMENT 'Типы топливных операций'");
        $this->uploadData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fuel_tank_flow_types');
    }

    public function uploadData()
    {
        DB::table('fuel_tank_flow_types')->insert([
            [
                'name' => 'Поступление',
                'slug' => 'income',
            ],
            [
                'name' => 'Расход',
                'slug' => 'outcome',
            ],
            [
                'name' => 'Корректировка',
                'slug' => 'adjustment',
            ],
        ]);
    }
};
