<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractorContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractor_contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contractor_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('patronymic')->nullable();
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number');
            $table->string('note')->nullable();
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
        Schema::dropIfExists('contractor_contacts');
    }
}
