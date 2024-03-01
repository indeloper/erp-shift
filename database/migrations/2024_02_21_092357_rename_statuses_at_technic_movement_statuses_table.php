<?php

use App\Models\TechAcc\TechnicMovementStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameStatusesAtTechnicMovementStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technic_movement_statuses', function (Blueprint $table) {
            TechnicMovementStatus::where('name', 'Заявка создана')
                ->update([
                    'name' => 'Новая заявка'
                ]);
            TechnicMovementStatus::where('name', 'Перевозчик найден')
                ->update([
                    'name' => 'Транспортировка запланирована',
                    'slug' => 'transportationPlanned'
                ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

}
