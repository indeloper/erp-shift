<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrigadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brigades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('number');
            $table->smallInteger('direction');
            $table->unsignedInteger('foreman_id')->nullable()->index();
            $table->unsignedInteger('user_id')->index();
            $table->softDeletes();
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
        Schema::dropIfExists('brigades');
    }
}
