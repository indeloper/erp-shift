<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('short_name_project_objects',
            function (Blueprint $table) {
                $table->integer('project_object_id')->index()->unsigned()
                    ->comment('Идентификатор объекта');

                $table->string('objectName')->nullable();
                $table->string('objectCaption')->nullable();
                $table->string('postalCode')->nullable();
                $table->string('city')->nullable();
                $table->string('street')->nullable();
                $table->string('section')->nullable();
                $table->string('building')->nullable();
                $table->string('housing')->nullable();
                $table->string('letter')->nullable();
                $table->string('construction')->nullable();
                $table->string('stead')->nullable();
                $table->string('queue')->nullable();
                $table->string('lot')->nullable();
                $table->string('stage')->nullable();
                $table->string('housingArea')->nullable();
                $table->string('cadastralNumber')->nullable();

                $table->foreign('project_object_id')->references('id')
                    ->on('project_objects');
            });
    }

};
