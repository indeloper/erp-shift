<?php

use App\Models\q3wMaterial\q3wMaterial;
use App\Models\q3wMaterial\q3wMaterialStandard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSelectionCounterColumnToMaterialStandards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('q3w_material_standards', function (Blueprint $table) {
            $table->bigInteger('selection_counter')->unsigned()->default(0)->comment("Cчетчик выборы эталона для ранжирования по поулярности");
        });

        $countOfStandardsInMaterials = q3wMaterial::select([DB::Raw('count(id) as standard_count'), 'standard_id'])
            ->groupBy('standard_id')
            ->get();

        foreach($countOfStandardsInMaterials as $standard){
            $materialStandard = q3wMaterialStandard::find($standard->standard_id);
            if ($materialStandard) {
                $materialStandard->selection_counter = $standard->standard_count;
                $materialStandard->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('q3w_material_standards', function (Blueprint $table) {
            $table->dropColumn('selection_counter');
        });
    }
}
