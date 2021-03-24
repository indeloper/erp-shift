<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQ3wMaterialStandardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('q3w_material_standards', function (Blueprint $table) {
            $table->increments('id')->comment('Уникальный идентификатор');
            $table->string('name')->index()->unique()->comment('Наименование эталона');
            $table->integer('material_type')->unsigned()->comment('Тип материала');
            $table->double('weight')->unsigned()->comment('Вес за 1 единицу измерения');
            $table->text('description')->nullable()->comment('Описание');

            $table->timestamps();
            $table->softDeletes();

            $table->index('material_type');
        });

        Schema::table('q3w_material_standards', function($table) {
            $table->foreign('material_type')->references('id')->on('q3w_material_types');
        });

        /*Импорт из эталонов старой версии материального учета*/
        /*$materialReferences = ManualReference::all();

        foreach ($materialReferences as $materialReference) {
            $categoryWeightAttributesIds = array(2=>109, //Шпунт кг
                3=>190, //Арматура кг
                4=>127, //Балка кг
                5=>132, //Лист г/к кг
                6=>129, //Швеллер кг
                7>128, //Труба прямошовная кг
                8=>130, //Труба бесшовная кг
                9=>131, //Труба профильная прямоугольная кг
                10=>170, //Угловые элементы кг
                11=>191, //Уголок г/к кг
                12=>0, //Цельные сваи кг
                14=>0, //Составные сваи кг
                16=>154, //Анкер кг
                19=>0, //Доска кг
                21=>0, //Песок/щебень кг
                22=>193, //Опорный столик 35Б1/35Ш1/35Ш2 т
                23=>0, //Бетон
                24=>0, //Пленка
                25=>0, //Дополнительные материалы
                26=>198, //Шпилька т
                27=>199, //Гайка т
                28=>0, //Труба ПВХ (ПНД)
                29=>0, //Прочее
                30=>0 //Сваи из труб
            );

            $materialCategoryId = $materialReference->category_id;
            $materialCategory = ManualMaterialCategory::find($materialCategoryId);
            $materialReferenceParameter = ManualReferenceParameter::where('attr_id', $categoryWeightAttributesIds[$materialCategoryId])
                ->where('manual_reference_id', $materialReference->id)
                ->first();

            if (isset($materialReferenceParameter)){
                $materialWeight = $materialReferenceParameter->value;
            } else {
                $materialWeight = 0;
            }

            switch ($materialCategoryId) {
                case 26:
                case 27:
                case 22:
                $materialWeight = round($materialWeight, 5);
                    break;
                default:
                    $materialWeight = round($materialWeight / 1000, 5);
            }

            $materialStandard = q3wMaterialStandard::where('name', $materialReference->name)->count();

            if ($materialStandard == 0) {

                $materialStandard = new q3wMaterialStandard();
                $materialStandard->name = $materialReference->name;
                $materialStandard->material_type = q3wMaterialType::where('name', $materialCategory->name)->first()->id;

                $materialStandard->weight = $materialWeight;
                $materialStandard->description = $materialReference->description;

                $materialStandard->save();
            }
        }*/

        (new materialsSeeder)->run();
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('q3w_material_standards');
    }
}
