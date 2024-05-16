<?php

use App\Models\q3wMaterial\q3wMaterialTransformationType;
use Illuminate\Database\Migrations\Migration;

class AddNewTransformationType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $transformationType = new q3wMaterialTransformationType();
        $transformationType->value = 'Изготовление клиновидного';
        $transformationType->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        q3wMaterialTransformationType::where('value', 'like', 'Изготовление клиновидного')->first()->forceDelete();
    }
}
