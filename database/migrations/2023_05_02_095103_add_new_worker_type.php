<?php

use App\Models\LaborSafety\LaborSafetyWorkerType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewWorkerType_ extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $laborSafetyWorkerTypesArray = [
            'Замещающий геодезиста'
        ];

        foreach ($laborSafetyWorkerTypesArray as $laborSafetyWorkerTypesElement) {
            $laborSafetyWorkerTypes = new LaborSafetyWorkerType([
                'name' => $laborSafetyWorkerTypesElement
            ]);
            $laborSafetyWorkerTypes->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        LaborSafetyWorkerType::where('name', '=', 'Замещающий геодезиста')->forceDelete();
    }
}
