<?php

use App\Models\q3wMaterial\q3wMaterialTransformationType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewTransformationTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $transformationType = new q3wMaterialTransformationType();
        $transformationType -> value = "Изготовление спаренного";
        $transformationType -> save();

        $transformationType = new q3wMaterialTransformationType();
        $transformationType -> value = "Резка углового";
        $transformationType -> save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        q3wMaterialTransformationType::where('value', 'like', 'Изготовление спаренного')->first()->forceDelete();
        q3wMaterialTransformationType::where('value', 'like', 'Резка углового')->first()->forceDelete();
    }
}
