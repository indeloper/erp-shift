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
        Schema::create('contractor_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('Наименование');
            $table->string('slug')->comment('Кодовое наименование');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE contractor_types COMMENT 'Типы контрагентов'");
        $this->uploadData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contractor_types');
    }

    public function uploadData()
    {
        DB::table('contractor_types')->insert([
            [
                'name' => 'Заказчик',
                'slug' => 'customer',
            ],
            [
                'name' => 'Подрядчик',
                'slug' => 'executor',
            ],
            [
                'name' => 'Поставщик материалов',
                'slug' => 'materials_supplier',
            ],
            [
                'name' => 'Поставщик топлива',
                'slug' => 'fuel_supplier',
            ],
        ]);
    }
};
