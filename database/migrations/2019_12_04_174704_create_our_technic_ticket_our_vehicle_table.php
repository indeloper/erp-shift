<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOurTechnicTicketOurVehicleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('our_technic_ticket_our_vehicle', function (Blueprint $table) {
            $table->unsignedInteger('our_technic_ticket_id');
            $table->unsignedInteger('our_vehicle_id');
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
        Schema::dropIfExists('our_technic_ticket_our_vehicle');
    }
}
