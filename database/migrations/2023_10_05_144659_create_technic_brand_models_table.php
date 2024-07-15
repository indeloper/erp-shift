<?php

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
        Schema::create('technic_brand_models', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('technic_brand_id')->unsigned()->comment('ID бренда техники');
            $table->foreign('technic_brand_id')->references('id')->on('technic_brands');

            $table->string('name')->comment('Наименование');
            $table->string('description')->nullable()->comment('Описание');

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE technic_brand_models COMMENT 'Модели техники'");
        $this->uploadData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technic_brand_models');
    }

    public function uploadData()
    {
        DB::table('technic_brand_models')->insert([
            [
                'technic_brand_id' => 1,
                'name' => '350С',
                'description' => 'Энергоблок',
            ],
            [
                'technic_brand_id' => 1,
                'name' => '30Н1А',
                'description' => 'Погружатель',
            ],
            [
                'technic_brand_id' => 1,
                'name' => '600D',
                'description' => 'Энергоблок',
            ],

            [
                'technic_brand_id' => 2,
                'name' => 'РР521',
                'description' => 'Энергоблок',
            ],
            [
                'technic_brand_id' => 2,
                'name' => 'SVR18VM',
                'description' => 'Погружатель',
            ],
            [
                'technic_brand_id' => 3,
                'name' => 'SPU-15001W',
                'description' => '',
            ],
            [
                'technic_brand_id' => 4,
                'name' => 'ТЕ-200',
                'description' => '',
            ],

        ]);
    }
};
