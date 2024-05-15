<?php

use App\Models\TechAcc\TechnicMovementStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
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
    public function down(): void
    {
        //
    }
};
