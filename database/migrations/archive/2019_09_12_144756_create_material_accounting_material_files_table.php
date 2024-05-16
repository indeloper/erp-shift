<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('material_accounting_material_files', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('operation_id');
            $table->unsignedInteger('operation_material_id');
            $table->string('file_name');
            $table->string('path');
            $table->unsignedInteger('type')->default(1); // docs or images
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('material_accounting_material_files');
    }
};
