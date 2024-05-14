<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialAccountingOperationResponsibleUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('material_accounting_operation_responsible_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('operation_id');
            $table->unsignedInteger('user_id');
            $table->boolean('type')->default(0);
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
        Schema::dropIfExists('material_accounting_operation_responsible_users');
    }
}
