<?php

use App\Models\TechAcc\TechnicMovementStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameStatuses2AtTechnicMovementStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technic_movement_statuses', function (Blueprint $table) {
            TechnicMovementStatus::where('slug', 'created')
                ->update([
                    'name' => 'Новая',
                ]);
        });

        Schema::table('technic_movement_statuses', function (Blueprint $table) {
            TechnicMovementStatus::where('slug', 'transportationPlanned')
                ->update([
                    'name' => 'Запланирована',
                ]);
        });

        Schema::table('technic_movement_statuses', function (Blueprint $table) {
            TechnicMovementStatus::where('slug', 'inProgress')
                ->update([
                    'name' => 'Транспортировка',
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
