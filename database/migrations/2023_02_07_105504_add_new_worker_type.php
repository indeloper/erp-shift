<?php

use App\Models\LaborSafety\LaborSafetyWorkerType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
     */
    public function down(): void
    {
        LaborSafetyWorkerType::where('name', '=', 'Геодезист')->forceDelete();
    }
};
