<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractKeyDatesPreselectedNamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_key_dates_preselected_names', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('value');
            $table->timestamps();
            $table->softDeletes();
        });

        // fill values from Ezerskaya doc
        DB::table('contract_key_dates_preselected_names')->insert([
            ['value' => 'Начало работ'],
            ['value' => 'Окончание работ'],
            ['value' => 'Передача фронта работ'],
            ['value' => 'Аванс'],
            ['value' => 'Оплата обеспечительного платежа'],
            ['value' => 'Оплата КС'],
            ['value' => 'Оплата аренды'],
            ['value' => 'Оплата сверхнормативной аренды'],
            ['value' => 'Подача ППР'],
            ['value' => 'Возврат обеспечения'],
            ['value' => 'Гарантийный период'],
            ['value' => 'Срок аренды'],
            ['value' => 'Подача ИД'],
            ['value' => 'Подача КС'],
            ['value' => 'Начало поставки'],
            ['value' => 'Окончание поставки'],
            ['value' => 'Оплата поставки'],
            ['value' => 'Предоставление накладных'],
            ['value' => 'Начало аренды'],
            ['value' => 'Окончание аренды'],
            ['value' => 'Стоимость аренды'],
            ['value' => 'Срок оплаты акта'],
            ['value' => 'Оплата простоя'],
            ['value' => 'Количество дней неоплачиваемого простоя'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_key_dates_preselected_names');
    }
}