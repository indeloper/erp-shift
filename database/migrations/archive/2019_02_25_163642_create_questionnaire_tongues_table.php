<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionnaireTonguesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questionnaire_tongues', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('questionnaire_id');
            $table->string('type')->nullable();
            $table->string('count')->nullable();
            $table->string('length')->nullable();
            $table->string('dive_type')->nullable();
            $table->string('term')->nullable();
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
        Schema::dropIfExists('questionnaire_tongues');
    }
}
