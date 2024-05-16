<?php

use App\Models\q3wMaterial\operations\q3wTransformOperationStage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $transformStageName = 'Технологические потери исходных материалов';

        $transferOperationStage = new q3wTransformOperationStage();
        $transferOperationStage->name = $transformStageName;
        $transferOperationStage->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $transformStageName = 'Технологические потери исходных материалов';

        q3wTransformOperationStage::where('name', 'like', $transformStageName)->forceDelete();
    }
};
