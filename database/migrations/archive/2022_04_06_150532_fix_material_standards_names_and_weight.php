<?php

use App\Models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wMaterialType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $arrayOfMaterialTypesIds = [5, 6, 7];

        foreach ($arrayOfMaterialTypesIds as $materialTypesId) {
            $standards = q3wMaterialStandard::where('material_type', $materialTypesId)->get();
            foreach ($standards as $standard) {
                $standard->weight = round($standard->weight * 10, 5);
                $standard->save();
            }
        }

        $arrayOfMaterialTypesIds = [3, 4, 8];

        foreach ($arrayOfMaterialTypesIds as $materialTypesId) {
            $materialType = q3wMaterialType::find($materialTypesId);
            $standards = q3wMaterialStandard::where('material_type', $materialTypesId)->get();
            foreach ($standards as $standard) {
                $standard->name = $materialType->name.' '.$standard->name;
                $standard->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $arrayOfMaterialTypesIds = [5, 6, 7];

        foreach ($arrayOfMaterialTypesIds as $materialTypesId) {
            $standards = q3wMaterialStandard::where('material_type', $materialTypesId)->get();
            foreach ($standards as $standard) {
                $standard->weight = round($standard->weight / 10, 5);
                $standard->save();
            }
        }

        $arrayOfMaterialTypesIds = [3, 4, 8];

        foreach ($arrayOfMaterialTypesIds as $materialTypesId) {
            $materialType = q3wMaterialType::find($materialTypesId);
            $standards = q3wMaterialStandard::where('material_type', $materialTypesId)->get();
            foreach ($standards as $standard) {
                $standard->name = str_replace($materialType->name.' ', '', $standard->name);
                $standard->save();
            }
        }
    }
};
