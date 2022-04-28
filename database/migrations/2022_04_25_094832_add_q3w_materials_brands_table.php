<?php

use App\Models\q3wMaterial\q3wMaterialBrand;
use App\Models\q3wMaterial\q3wMaterialBrandsRelation;
use App\Models\q3wMaterial\q3wMaterialBrandType;
use App\Models\q3wMaterial\q3wMaterialStandard;
use App\Models\q3wMaterial\q3wStandardPropertiesRelations;
use App\Models\q3wMaterial\q3wStandardProperty;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQ3wMaterialsBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('q3w_material_brand_types', function (Blueprint $table) {
            $table->increments('id')->comment('Уникальный идентификатор');
            $table->string('name')->comment('Наименование типа марки');

            $table->timestamps();
            $table->softDeletes();
        });

        $brandTypeNames = ['VL/GU/PU', 'Л5', 'AZ'];

        foreach ($brandTypeNames as $brandTypeName) {
            $brandType = new q3wMaterialBrandType();
            $brandType -> name = $brandTypeName;
            $brandType -> save();
        }

        Schema::create('q3w_material_brands', function (Blueprint $table) {
            $table->increments('id')->comment('Уникальный идентификатор');
            $table->integer('material_type_id')->unsigned()->comment('Тип материала');
            $table->integer('brand_type_id')->unsigned()->nullable()->comment('Тип марки материала');
            $table->string('name')->comment('Наименование марки');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('material_type_id')->references('id')->on('q3w_material_types');
            $table->foreign('brand_type_id')->references('id')->on('q3w_material_brands');
        });

        $brands = [['As 500-13', '1', ''],
            ['AU 23', '1', ''],
            ['AZ 12-770', '1', 'AZ'],
            ['AZ 13-770', '1', 'AZ'],
            ['AZ 14-770', '1', 'AZ'],
            ['AZ 18-700', '1', 'AZ'],
            ['AZ 18-800', '1', 'AZ'],
            ['AZ 24-700N', '1', 'AZ'],
            ['AZ 28-700', '1', 'AZ'],
            ['AZ 36-700N', '1', 'AZ'],
            ['AZ 48-580', '1', 'AZ'],
            ['AZ 48-700', '1', 'AZ'],
            ['GU 18N', '1', 'VL/GU/PU'],
            ['GU 22N', '1', 'VL/GU/PU'],
            ['GU 27N', '1', 'VL/GU/PU'],
            ['PU 22', '1', 'VL/GU/PU'],
            ['PU 28-1', '1', 'VL/GU/PU'],
            ['VL 603', '1', 'VL/GU/PU'],
            ['VL 606A', '1', 'VL/GU/PU'],
            ['Л4', '1', ''],
            ['Л5-УМ', '1', 'Л5'],
            ['⌀10', '2', ''],
            ['⌀12', '2', ''],
            ['⌀14', '2', ''],
            ['⌀16', '2', ''],
            ['⌀18', '2', ''],
            ['⌀20', '2', ''],
            ['⌀22', '2', ''],
            ['⌀25', '2', ''],
            ['⌀28', '2', ''],
            ['⌀32', '2', ''],
            ['⌀36', '2', ''],
            ['⌀40', '2', ''],
            ['⌀45', '2', ''],
            ['⌀50', '2', ''],
            ['⌀55', '2', ''],
            ['⌀6', '2', ''],
            ['⌀60', '2', ''],
            ['⌀70', '2', ''],
            ['⌀8', '2', ''],
            ['⌀80', '2', ''],
            ['10Б1', '3', ''],
            ['12Б1', '3', ''],
            ['12Б2', '3', ''],
            ['14Б1', '3', ''],
            ['14Б2', '3', ''],
            ['16Б1', '3', ''],
            ['16Б2', '3', ''],
            ['18Б1', '3', ''],
            ['18Б2', '3', ''],
            ['20Б1', '3', ''],
            ['20К1', '3', ''],
            ['20К2', '3', ''],
            ['20Ш1', '3', ''],
            ['25Б1', '3', ''],
            ['25К1', '3', ''],
            ['25К2', '3', ''],
            ['25К3', '3', ''],
            ['25Ш1', '3', ''],
            ['30Б1', '3', ''],
            ['30Б2', '3', ''],
            ['30К1', '3', ''],
            ['30К2', '3', ''],
            ['30К3', '3', ''],
            ['30К4', '3', ''],
            ['30Ш1', '3', ''],
            ['30Ш2', '3', ''],
            ['30Ш3', '3', ''],
            ['35Б1', '3', ''],
            ['35Б2', '3', ''],
            ['35К1', '3', ''],
            ['35К2', '3', ''],
            ['35Ш1', '3', ''],
            ['35Ш2', '3', ''],
            ['35Ш3', '3', ''],
            ['40Б1', '3', ''],
            ['40Б2', '3', ''],
            ['40К1', '3', ''],
            ['40К2', '3', ''],
            ['40К3', '3', ''],
            ['40К4', '3', ''],
            ['40К5', '3', ''],
            ['40Ш1', '3', ''],
            ['40Ш2', '3', ''],
            ['40Ш3', '3', ''],
            ['45Б1', '3', ''],
            ['45Б2', '3', ''],
            ['50Б1', '3', ''],
            ['50Б2', '3', ''],
            ['50Ш1', '3', ''],
            ['50Ш2', '3', ''],
            ['50Ш3', '3', ''],
            ['50Ш4', '3', ''],
            ['55Б1', '3', ''],
            ['55Б2', '3', ''],
            ['60Б1', '3', ''],
            ['60Б2', '3', ''],
            ['60Ш1', '3', ''],
            ['60Ш2', '3', ''],
            ['60Ш3', '3', ''],
            ['60Ш4', '3', ''],
            ['70Б1', '3', ''],
            ['70Б2', '3', ''],
            ['70Ш1', '3', ''],
            ['70Ш2', '3', ''],
            ['70Ш3', '3', ''],
            ['70Ш4', '3', ''],
            ['70Ш5', '3', ''],
            ['1020x10', '4', ''],
            ['1020x11', '4', ''],
            ['1020x12', '4', ''],
            ['1020x13', '4', ''],
            ['1020x14', '4', ''],
            ['1020x16', '4', ''],
            ['1020x20', '4', ''],
            ['1020x8', '4', ''],
            ['1020x9', '4', ''],
            ['1120x10', '4', ''],
            ['1120x11', '4', ''],
            ['1120x12', '4', ''],
            ['1120x13', '4', ''],
            ['1120x14', '4', ''],
            ['1120x16', '4', ''],
            ['1120x20', '4', ''],
            ['1120x8', '4', ''],
            ['1120x9', '4', ''],
            ['1220x10', '4', ''],
            ['1220x11', '4', ''],
            ['1220x12', '4', ''],
            ['1220x14', '4', ''],
            ['1220x16', '4', ''],
            ['1220x20', '4', ''],
            ['127x10', '4', ''],
            ['127x8', '4', ''],
            ['127x9', '4', ''],
            ['1420x18', '4', ''],
            ['1420x19', '4', ''],
            ['1420x20', '4', ''],
            ['377x10', '4', ''],
            ['377x6', '4', ''],
            ['377x7', '4', ''],
            ['377x8', '4', ''],
            ['377x9', '4', ''],
            ['426x10', '4', ''],
            ['426x11', '4', ''],
            ['426x12', '4', ''],
            ['426x7', '4', ''],
            ['426x8', '4', ''],
            ['426x9', '4', ''],
            ['530x10', '4', ''],
            ['530x11', '4', ''],
            ['530x12', '4', ''],
            ['530x6', '4', ''],
            ['530x7', '4', ''],
            ['530x8', '4', ''],
            ['530x9', '4', ''],
            ['630x10', '4', ''],
            ['630x11', '4', ''],
            ['630x12', '4', ''],
            ['630x13', '4', ''],
            ['630x14', '4', ''],
            ['630x7', '4', ''],
            ['630x8', '4', ''],
            ['630x9', '4', ''],
            ['720x10', '4', ''],
            ['720x11', '4', ''],
            ['720x12', '4', ''],
            ['720x13', '4', ''],
            ['720x14', '4', ''],
            ['720x7', '4', ''],
            ['720x8', '4', ''],
            ['720x9', '4', ''],
            ['820x10', '4', ''],
            ['820x11', '4', ''],
            ['820x12', '4', ''],
            ['820x13', '4', ''],
            ['820x14', '4', ''],
            ['820x16', '4', ''],
            ['820x7', '4', ''],
            ['820x8', '4', ''],
            ['820x9', '4', ''],
            ['10П', '5', ''],
            ['10У', '5', ''],
            ['12П', '5', ''],
            ['12У', '5', ''],
            ['14П', '5', ''],
            ['14У', '5', ''],
            ['16aП', '5', ''],
            ['16aУ', '5', ''],
            ['16П', '5', ''],
            ['16У', '5', ''],
            ['18aП', '5', ''],
            ['18aУ', '5', ''],
            ['18П', '5', ''],
            ['18У', '5', ''],
            ['20П', '5', ''],
            ['20У', '5', ''],
            ['22П', '5', ''],
            ['22У', '5', ''],
            ['24П', '5', ''],
            ['24У', '5', ''],
            ['27П', '5', ''],
            ['27У', '5', ''],
            ['30П', '5', ''],
            ['30У', '5', ''],
            ['33П', '5', ''],
            ['33У', '5', ''],
            ['36П', '5', ''],
            ['36У', '5', ''],
            ['40П', '5', ''],
            ['40У', '5', ''],
            ['5П', '5', ''],
            ['5У', '5', ''],
            ['6.5П', '5', ''],
            ['6.5У', '5', ''],
            ['8П', '5', ''],
            ['8У', '5', ''],
            ['E22', '6', ''],
            ['LV22', '6', ''],
            ['С9', '6', ''],
            ['E20', '6', ''],
            ['LV8', '6', ''],
            ['С14', '6', ''],
            ['DELTA 13', '6', ''],
            ['LV20n', '6', ''],
            ['LV-Omega', '6', ''],
            ['Omega 18', '6', ''],
            ['Omega 17', '6', ''],
            ['12', '7', ''],
            ['8', '7', ''],
            ['10', '7', ''],
            ['14', '7', ''],
            ['16', '7', ''],
            ['20', '7', ''],
            ['22', '7', ''],
            ['30', '7', ''],
            ['50', '7', ''],
            ['28', '7', ''],
            ['25', '7', ''],
            ['32', '7', ''],
            ['100', '7', ''],
            ['24', '7', ''],
            ['5', '7', ''],
            ['6', '7', ''],
            ['3', '7', ''],
            ['40x40x3', '8', ''],
            ['40x40x3.5', '8', ''],
            ['40x40x4', '8', ''],
            ['40x40x5', '8', ''],
            ['40x40x6', '8', ''],
            ['50x50x3', '8', ''],
            ['50x50x3.5', '8', ''],
            ['50x50x4', '8', ''],
            ['50x50x5', '8', ''],
            ['50x50x6', '8', ''],
            ['50x50x7', '8', ''],
            ['50x50x8', '8', ''],
            ['60x60x3.5', '8', ''],
            ['60x60x5', '8', ''],
            ['60x60x6', '8', ''],
            ['60x60x7', '8', ''],
            ['60x60x8', '8', ''],
            ['60x60x3', '8', ''],
            ['60x60x4', '8', ''],
            ['70x70x3', '8', ''],
            ['70x70x4', '8', ''],
            ['ЗД на болтах ⌀530-630', '9', ''],
            ['ЗД-1', '9', ''],
            ['ЗД-2', '9', ''],
            ['ЗД-3', '9', '']];

        foreach ($brands as $brand){
            if (empty($brand[2])) {
                $materialBrandTypeId = null;
            } else {
                $materialBrandTypeId = q3wMaterialBrandType::where('name', 'like', $brand[2])->get()->first()->id;
            }

            $materialBrand = new q3wMaterialBrand();
            $materialBrand -> name = $brand[0];
            $materialBrand -> material_type_id = $brand[1];
            $materialBrand -> brand_type_id = $materialBrandTypeId;
            $materialBrand -> save();
        }

        Schema::create('q3w_material_brands_relations', function (Blueprint $table) {
            $table->increments('id')->comment('Уникальный идентификатор');
            $table->integer('brand_id')->unsigned()->comment('Тип марки материала');
            $table->integer('standard_id')->unsigned()->comment('Тип марки материала');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('brand_id')->references('id')->on('q3w_material_brands');
            $table->foreign('standard_id')->references('id')->on('q3w_material_standards');
        });

        $materialBrands = q3wMaterialBrand::all();

        foreach ($materialBrands as $materialBrand){
            if ($materialBrand->material_type_id == 7) {
                $materialStandards = q3wMaterialStandard::where('name', 'like', '%к ' . $materialBrand->name)->get();
            } else {
                $materialStandards = q3wMaterialStandard::where('name', 'like', '%' . $materialBrand->name . '%')->get();
            }
            foreach ($materialStandards as $materialStandard) {
                $brandRelation = new q3wMaterialBrandsRelation();
                $brandRelation->brand_id = $materialBrand->id;
                $brandRelation->standard_id = $materialStandard->id;
                $brandRelation->save();
            }
        }

        //AZ 24-700
        $materialBrand = new q3wMaterialBrand([
            'name' => 'AZ 24-700',
            'material_type_id' => 1,
            'brand_type_id' => q3wMaterialBrandType::where('name', 'like', 'AZ')->first()->id
        ]);
        $materialBrand->save();

        $materialStandards = q3wMaterialStandard::where('name', 'like', '%AZ 24-700')->get();
        foreach ($materialStandards as $materialStandard) {
            $brandRelation = new q3wMaterialBrandsRelation();
            $brandRelation->brand_id = $materialBrand->id;
            $brandRelation->standard_id = $materialStandard->id;
            $brandRelation->save();
        }

        //Л5
        $materialBrand = new q3wMaterialBrand([
            'name' => 'Л5',
            'material_type_id' => 1,
            'brand_type_id' => q3wMaterialBrandType::where('name', 'like', 'Л5')->first()->id
        ]);
        $materialBrand->save();

        $materialStandards = q3wMaterialStandard::where('name', 'like', '%Л5')
            ->orWhere('name', 'like', '%Л5 %')
            ->get();
        foreach ($materialStandards as $materialStandard) {
            $brandRelation = new q3wMaterialBrandsRelation();
            $brandRelation->brand_id = $materialBrand->id;
            $brandRelation->standard_id = $materialStandard->id;
            $brandRelation->save();
        }

        //Замок-обойма ОБ
        $materialBrand = new q3wMaterialBrand([
            'name' => 'Замок-обойма ОБ',
            'material_type_id' => 6,
            'brand_type_id' => null
        ]);
        $materialBrand->save();

        $materialStandards = q3wMaterialStandard::where('name', 'like', 'Замок-обойма ОБ')
            ->get();
        foreach ($materialStandards as $materialStandard) {
            $brandRelation = new q3wMaterialBrandsRelation();
            $brandRelation->brand_id = $materialBrand->id;
            $brandRelation->standard_id = $materialStandard->id;
            $brandRelation->save();
        }


        Schema::create('q3w_standard_properties', function (Blueprint $table) {
            $table->increments('id')->comment('Уникальный идентификатор');
            $table->string('name')->comment('Наименование свойства');
            $table->timestamps();
            $table->softDeletes();
        });

        $standardPropertiesNames = ['Клиновидный', 'Обрезок', 'С замком', 'С листом', 'С трубой', 'Спаренный', 'Стыкованный', 'Угловой'];

        foreach ($standardPropertiesNames as $standardPropertiesName) {
            $standardProperty = new q3wStandardProperty();
            $standardProperty -> name = $standardPropertiesName;
            $standardProperty -> save();
        }

        Schema::create('q3w_standard_properties_relations', function (Blueprint $table) {
            $table->increments('id')->comment('Уникальный идентификатор');
            $table->integer('standard_property_id')->unsigned()->comment('Свойство эталона');
            $table->integer('standard_id')->unsigned()->comment('Эталон');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('standard_property_id')->references('id')->on('q3w_standard_properties');
            $table->foreign('standard_id')->references('id')->on('q3w_material_standards');
        });

        $standardPropertyId = q3wStandardProperty::where('name', 'like', 'Клиновидный')->first()->id;
        $materialStandards = q3wMaterialStandard::where('name', 'like', '%клин%')->get();

        foreach ($materialStandards as $standard){
            $propertyRelation = new q3wStandardPropertiesRelations();
            $propertyRelation->standard_property_id = $standardPropertyId;
            $propertyRelation->standard_id = $standard->id;
            $propertyRelation->save();
        }

        $standardPropertyId = q3wStandardProperty::where('name', 'like', 'Обрезок')->first()->id;
        $materialStandards = q3wMaterialStandard::where('name', 'like', '%обрез%')->get();

        foreach ($materialStandards as $standard){
            $propertyRelation = new q3wStandardPropertiesRelations();
            $propertyRelation->standard_property_id = $standardPropertyId;
            $propertyRelation->standard_id = $standard->id;
            $propertyRelation->save();
        }

        $standardPropertyId = q3wStandardProperty::where('name', 'like', 'С замком')->first()->id;
        $materialStandards = q3wMaterialStandard::where('name', 'like', '%замком%')->get();

        foreach ($materialStandards as $standard){
            $propertyRelation = new q3wStandardPropertiesRelations();
            $propertyRelation->standard_property_id = $standardPropertyId;
            $propertyRelation->standard_id = $standard->id;
            $propertyRelation->save();
        }

        $standardPropertyId = q3wStandardProperty::where('name', 'like', 'С листом')->first()->id;
        $materialStandards = q3wMaterialStandard::where('name', 'like', '%с лист%')->get();

        foreach ($materialStandards as $standard){
            $propertyRelation = new q3wStandardPropertiesRelations();
            $propertyRelation->standard_property_id = $standardPropertyId;
            $propertyRelation->standard_id = $standard->id;
            $propertyRelation->save();
        }

        $standardPropertyId = q3wStandardProperty::where('name', 'like', 'С трубой')->first()->id;
        $materialStandards = q3wMaterialStandard::where('name', 'like', '%с труб%')->get();

        foreach ($materialStandards as $standard){
            $propertyRelation = new q3wStandardPropertiesRelations();
            $propertyRelation->standard_property_id = $standardPropertyId;
            $propertyRelation->standard_id = $standard->id;
            $propertyRelation->save();
        }

        $standardPropertyId = q3wStandardProperty::where('name', 'like', 'Спаренный')->first()->id;
        $materialStandards = q3wMaterialStandard::where('name', 'like', '%спаренный%')->get();

        foreach ($materialStandards as $standard){
            $propertyRelation = new q3wStandardPropertiesRelations();
            $propertyRelation->standard_property_id = $standardPropertyId;
            $propertyRelation->standard_id = $standard->id;
            $propertyRelation->save();
        }

        $standardPropertyId = q3wStandardProperty::where('name', 'like', 'Стыкованный')->first()->id;
        $materialStandards = q3wMaterialStandard::where('name', 'like', '%стыков%')->get();

        foreach ($materialStandards as $standard){
            $propertyRelation = new q3wStandardPropertiesRelations();
            $propertyRelation->standard_property_id = $standardPropertyId;
            $propertyRelation->standard_id = $standard->id;
            $propertyRelation->save();
        }

        $standardPropertyId = q3wStandardProperty::where('name', 'like', 'Угловой')->first()->id;
        $materialStandards = q3wMaterialStandard::where('name', 'like', '%Угловой ш%')->get();

        foreach ($materialStandards as $standard){
            $propertyRelation = new q3wStandardPropertiesRelations();
            $propertyRelation->standard_property_id = $standardPropertyId;
            $propertyRelation->standard_id = $standard->id;
            $propertyRelation->save();
        }

        $standardPropertyId = q3wStandardProperty::where('name', 'like', 'Угловой')->first()->id;
        $materialStandards = q3wMaterialStandard::where('name', 'like', '%углового ш%')->get();

        foreach ($materialStandards as $standard){
            $propertyRelation = new q3wStandardPropertiesRelations();
            $propertyRelation->standard_property_id = $standardPropertyId;
            $propertyRelation->standard_id = $standard->id;
            $propertyRelation->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('q3w_standard_properties_relations');
        Schema::dropIfExists('q3w_standard_properties');

        Schema::dropIfExists('q3w_material_brands_relations');
        Schema::dropIfExists('q3w_material_brand_types');
        Schema::dropIfExists('q3w_material_brands');

    }
}
