<?php

use App\Models\LaborSafety\LaborSafetyWorkerType;
use Illuminate\Database\Migrations\Migration;

class AddNewWorkerType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $laborSafetyWorkerTypesArray = [
            'Геодезист',
        ];

        foreach ($laborSafetyWorkerTypesArray as $laborSafetyWorkerTypesElement) {
            $laborSafetyWorkerTypes = new LaborSafetyWorkerType([
                'name' => $laborSafetyWorkerTypesElement,
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
        LaborSafetyWorkerType::where('name', '=', 'Геодезист')->forceDelete();
    }
}
