<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTariffRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::beginTransaction();

        Schema::create('tariff_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedInteger('type');
            $table->unsignedInteger('user_id')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('tariff_rates')->insert([
            [
                'name' => 'Обычный час',
                'type' => 1,
                'created_at' => now(),
                'user_id' => 1
            ],
            [
                'name' => 'Переработка',
                'type' => 1,
                'created_at' => now(),
                'user_id' => 1
            ],
            [
                'name' => 'Монтаж крепления',
                'type' => 1,
                'created_at' => now(),
                'user_id' => 1
            ],
            [
                'name' => 'Монтаж крепления (Переработка)',
                'type' => 1,
                'created_at' => now(),
                'user_id' => 1
            ],
            [
                'name' => 'Демонтаж крепления',
                'type' => 1,
                'created_at' => now(),
                'user_id' => 1
            ],
            [
                'name' => 'Демонтаж крепления (Переработка)',
                'type' => 1,
                'created_at' => now(),
                'user_id' => 1
            ],
            [
                'name' => 'Простой',
                'type' => 1,
                'created_at' => now(),
                'user_id' => 1
            ],
            [
                'name' => 'Погружение вибро',
                'type' => 2,
                'created_at' => now(),
                'user_id' => 1
            ],
            [
                'name' => 'Погружение вдвоём вибро',
                'type' => 2,
                'created_at' => now(),
                'user_id' => 1
            ],
            [
                'name' => 'Извлечение вибро',
                'type' => 2,
                'created_at' => now(),
                'user_id' => 1
            ],
            [
                'name' => 'Погружение статика',
                'type' => 2,
                'created_at' => now(),
                'user_id' => 1
            ],
            [
                'name' => 'Извлечение статика',
                'type' => 2,
                'created_at' => now(),
                'user_id' => 1
            ],
        ]);

        DB::commit();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tariff_rates');
    }
}
