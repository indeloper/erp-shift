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
        Schema::create('companies_legal_forms', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Уникальный идентификатор');
            $table->string('short_name')->comment('Краткое название формы предприятия');
            $table->string('name')->comment('Полное название формы предприятия');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE companies_legal_forms COMMENT 'Таблица с данными о формах предприятий'");

        DB::table('companies_legal_forms')->insert([
            [
                'short_name' => 'ООО',
                'name' => 'Общество с ограниченной ответственностью',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'short_name' => 'ОАО',
                'name' => 'Открытое акционерное общество',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'short_name' => 'ИП',
                'name' => 'Индивидуальный предприниматель',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'short_name' => 'ЗАО',
                'name' => 'Закрытое акционерное общество',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'short_name' => 'ПАО',
                'name' => 'Публичное акционерное общество',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'short_name' => 'ФГУП',
                'name' => 'Федеральное государственное унитарное предприятие',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('legal_form_id')->default(1)->after('company_1c_uid');
            $table->foreign('legal_form_id')->references('id')->on('companies_legal_forms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['legal_form_id']);
            $table->dropColumn('legal_form_id');
        });

        Schema::dropIfExists('companies_legal_forms');
    }
};
