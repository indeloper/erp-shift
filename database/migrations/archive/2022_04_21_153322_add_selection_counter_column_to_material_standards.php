<?php

use App\Models\q3wMaterial\q3wMaterial;
use App\Models\q3wMaterial\q3wMaterialStandard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('q3w_material_standards', function (Blueprint $table) {
            $table->bigInteger('selection_counter')->unsigned()->default(0)->comment('Cчетчик выборы эталона для ранжирования по поулярности');
        });

        $countOfStandardsInMaterials = q3wMaterial::select([DB::Raw('count(id) as standard_count'), 'standard_id'])
            ->groupBy('standard_id')
            ->get();

        foreach ($countOfStandardsInMaterials as $standard) {
            $materialStandard = q3wMaterialStandard::find($standard->standard_id);
            if ($materialStandard) {
                $materialStandard->selection_counter = $standard->standard_count;
                $materialStandard->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('q3w_material_standards', function (Blueprint $table) {
            $table->dropColumn('selection_counter');
        });
    }
};
