<?php

use App\Models\TechAcc\TechnicMovementStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('technic_movement_statuses', function (Blueprint $table) {
            TechnicMovementStatus::where('name', 'Заявка создана')
                ->update([
                    'name' => 'Новая заявка',
                ]);
            TechnicMovementStatus::where('name', 'Перевозчик найден')
                ->update([
                    'name' => 'Транспортировка запланирована',
                    'slug' => 'transportationPlanned',
                ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
