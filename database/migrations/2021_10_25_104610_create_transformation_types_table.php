<?php

use App\Models\q3wMaterial\q3wMaterialTransformationType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransformationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('q3w_material_transformation_types', function (Blueprint $table) {
            $table->increments('id')->comment('Уникальный идентификатор');
            $table->string('value')->comment('Значение');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('q3w_material_operations', function (Blueprint $table) {
            $table->integer('transformation_type_id')->nullable()->unsigned()->comment('Тип преобразования материала');

            $table->foreign('transformation_type_id')->references('id')->on('q3w_material_transformation_types');
        });

        $transformationTypeNames = ['Резка', 'Стыковка по длине', 'Изготовление угловых'];

        foreach ($transformationTypeNames as $transformationTypeName) {
            $transformationType = new q3wMaterialTransformationType();
            $transformationType -> value = $transformationTypeName;
            $transformationType -> save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('q3w_material_operations', function(Blueprint $table) {
            $table->dropForeign(['transformation_type_id']);
            $table->dropColumn(['transformation_type_id']);
        });

        Schema::dropIfExists('q3w_material_transformation_types');
    }
}