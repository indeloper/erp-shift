<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTechnicBrandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technic_brands', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable()->comment('Наименование');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE technic_brands COMMENT 'Бренды / марки техники'");
        $this->uploadData(); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('technic_brands');
    }

    public function uploadData()
    {
        DB::table('technic_brands')->insert([
            ['name'=>'РТС'],
            ['name'=>'OMS'],
            ['name'=>'TOMEN PILER'],
            ['name'=>'STILL WORKER'],
            ['name'=>'GIKEN'],
            ['name'=>'HITACHI'],
            ['name'=>'SUNWARD'],
            ['name'=>'SOILMEC'],
            ['name'=>'GROVE'],
            ['name'=>'ZOOMLION'],
            ['name'=>'TAKRAF'],
            ['name'=>'HIDROMEK'],
            ['name'=>'SDMO'],
            ['name'=>'INMESOL'],
        ]);
    }
}