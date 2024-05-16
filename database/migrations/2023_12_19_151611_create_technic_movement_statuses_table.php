<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTechnicMovementStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technic_movement_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('Наименование');
            $table->string('slug');
            $table->integer('sortOrder')->comment('Порядок сортировки');

            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('technic_movement_statuses')->insert($this->getNewEntrises());

        DB::statement("ALTER TABLE technic_movement_statuses COMMENT 'Статусы перемещений стоительной техники»'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('technic_movement_statuses');
    }

    public function getNewEntrises()
    {
        $newEntries = self::NEW_ENTRIES;
        foreach ($newEntries as $key => $newEntry) {
            $newEntries[$key]['created_at'] = now();
            $newEntries[$key]['updated_at'] = now();
        }

        return $newEntries;
    }

    const NEW_ENTRIES = [
        [
            'name' => 'Заявка создана',
            'slug' => 'created',
            'sortOrder' => 1,
        ],
        [
            'name' => 'Перевозчик найден',
            'slug' => 'carrierFound',
            'sortOrder' => 2,
        ],
        [
            'name' => 'В процессе перевозки',
            'slug' => 'inProgress',
            'sortOrder' => 3,
        ],
        [
            'name' => 'Исполнена',
            'slug' => 'completed',
            'sortOrder' => 4,
        ],
        [
            'name' => 'Отменена',
            'slug' => 'cancelled',
            'sortOrder' => 5,
        ],
    ];
}
